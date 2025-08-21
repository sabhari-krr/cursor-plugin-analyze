<?php

namespace RelayWp\Affiliate\Pro\Controllers\Api;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Response;
use RelayWp\Affiliate\App\Exception\ModelNotFoundException;
use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Services\Database;
use RelayWp\Affiliate\Core\Models\Program;
use RelayWp\Affiliate\Pro\Models\Program as ProgramPro;
use RelayWp\Affiliate\Core\ValidationRequest\Program\ProgramIDRequest;
use RelayWp\Affiliate\Pro\Resources\Program\ProgramRegistrationPageResource;
use RelayWp\Affiliate\Pro\ValidationRequest\CreateProgramPageRequest;

class PBAREditorController
{

    public static function getProgramCustomPageInfo(Request $request)
    {
        $request->validate(new ProgramIDRequest());

        try {
            $program_id = $request->get('program_id');

            $program = Program::query()->find($program_id);

            if (empty($program)) {
                throw new ModelNotFoundException('Model Not found');
            }

            return ProgramRegistrationPageResource::resource([$program]);
        } catch (ModelNotFoundException $exception) {
            PluginHelper::logError('Model Not Found', [__CLASS__, __FUNCTION__], $exception);
            return Response::error(PluginHelper::serverErrorMessage(), 404);
        } catch (\Exception | \Error $exception) {
            PluginHelper::logError('Error Occurred While Processing', [__CLASS__, __FUNCTION__], $exception);
            return Response::error(PluginHelper::serverErrorMessage());
        }
    }

    public static function updateProgramCustomFields(Request $request)
    {
        $request->validate(new CreateProgramPageRequest());

        Database::beginTransaction();
        try {
            $program_id = $request->get('program_id');
            $auto_approve = Functions::getBoolValue($request->get('auto_approve'));

            $program = Program::query()->findOrFail($program_id);

            $custom_field_shortcode = $program->custom_field_shortcode;
            $is_newly_created = false;

            $update_program_data = [];

            $update_program_data['auto_approve'] = $auto_approve;

            if (empty($custom_field_shortcode)) {
                $custom_field_shortcode = Functions::getUniqueKey($program->id);
                $update_program_data['custom_field_shortcode'] = $custom_field_shortcode;
                $is_newly_created = true;
            }

            $update_program_data['custom_affiliate_fields'] = ProgramPro::getCustomFieldDataForForm($request);

            Program::query()->update($update_program_data, [
                'id' => $program->id
            ]);


            $program = Program::query()->findOrFail($program_id);

            Database::commit();

            Response::success([
                'message' => $is_newly_created  ? __('Registration Page Created', 'relay-affiliate-marketing') : __("Registration Page Updated", 'relay-affiliate-marketing'),
                'is_newly_created' => $is_newly_created,
                'custom_field_data' => [
                    'shortcode' => ProgramPro::getRegistrationPageShortCode($program),
                ]
            ]);
        } catch (\Exception | \Error $exception) {
            Database::rollBack();
            PluginHelper::logError('Error Occurred While Processing', [__CLASS__, __FUNCTION__], $exception);
            return Response::error(PluginHelper::serverErrorMessage());
        }
    }
}
