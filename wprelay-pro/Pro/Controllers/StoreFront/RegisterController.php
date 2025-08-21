<?php

namespace RelayWp\Affiliate\Pro\Controllers\StoreFront;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Response;
use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Services\Database;
use RelayWp\Affiliate\App\Services\Settings;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\Core\Models\Member;
use RelayWp\Affiliate\Core\Models\Program;
use RelayWp\Affiliate\Pro\Models\Program as ProgramPro;
use RelayWp\Affiliate\Pro\ValidationRequest\Affiliate\AffiliateRegisterRequestForSpecificProgram;

class RegisterController
{
    public static function newRegistrationForSpecificProgram(Request $request)
    {

        if (apply_filters('rwpa_enable_spam_verification_services', true)) {
            PluginHelper::verifyGoogleRecaptcha($request);
        }

        $request->validate(new AffiliateRegisterRequestForSpecificProgram());

        Database::beginTransaction();

        try {

            $program_id = $request->get('program_id');

            $program = Program::query()->find($program_id);

            if (empty($program)) {
                Response::error([
                    'message' => __('Unable to Proceed Now. Linked Program Not Found', 'relay-affiliate-marketing')
                ], 404);
            }
            //create member
            $memberData = [];

            $memberData['first_name'] = $request->get('first_name');
            $memberData['last_name'] = $request->get('last_name');
            $memberData['email'] = $email = $request->get('email');
            $memberData['type'] = $type = 'affiliate';
            $memberData['created_at'] = Functions::currentUTCTime();
            $memberData['updated_at'] = Functions::currentUTCTime();

            $isInserted = Member::query()->create($memberData);

            if (!$isInserted) {
                Response::error(['message' => __('Unable to Create Member Affiliate', 'relay-affiliate-marketing')]);
            }

            $editor_fields = ProgramPro::getCustomFieldsData($program->custom_affiliate_fields);


            $fields = array_filter($editor_fields['fields'], function ($field) {
                return !$field['is_default'];
            });

            $affiliateData = [];

            $memberId = Member::query()->lastInsertedId();

            $affiliateData['status'] = $program->auto_approve ? 'approved' : 'pending';

            $affiliateData['member_id'] = $memberId;
            $affiliateData['program_id'] = $program->id;

            $affiliateData['created_at'] = Functions::currentUTCTime();
            $affiliateData['updated_at'] = Functions::currentUTCTime();

            $affiliate_program_specific_fields = [];

            foreach ($fields as $field) {
                $name = $field['field_name'];
                $value = $request->get($name, '');
                $affiliate_program_specific_fields[] = [
                    'value' => $value,
                    'label' => $field['label'],
                    'type' => $field['type']
                ];
            }

            if (!empty($affiliate_program_specific_fields)) {
                $affiliateData['meta_data'] = wp_json_encode($affiliate_program_specific_fields);
            }

            Affiliate::query()->create($affiliateData);
            $affiliateId = Affiliate::query()->lastInsertedId();


            $affiliate = Affiliate::query()->findOrFail($affiliateId);

            $member = Member::query()->find($memberId);

            Affiliate::autoApprove($affiliate->id);

            //refreshing here.
            $affiliate = Affiliate::query()->findOrFail($affiliateId);

            Database::commit();

            $send_affiliate_registered_email = apply_filters('rwpa_is_affiliate_registered_email_enabled', Settings::get('email_settings.admin_emails.affiliate_registered'));

            if ($send_affiliate_registered_email) {
                do_action('rwpa_send_affiliate_registered_email', [
                    'first_name' => $member->first_name,
                    'last_name' => $member->last_name,
                    'email' => $member->email,
                ]);
            }

            return Response::success([
                'message' => 'Affiliate Created Successfully',
                'affiliate' => [
                    'id' => $affiliateId
                ],
                'confirmation_html' => ProgramPro::getConfirmationHTML($program, $affiliate)
            ]);
        } catch (\Exception | \Error $exception) {
            Database::rollBack();
            PluginHelper::logError('Error Occurred While Processing', [__CLASS__, __FUNCTION__], $exception);
            return Response::error(PluginHelper::serverErrorMessage());
        }
    }
}
