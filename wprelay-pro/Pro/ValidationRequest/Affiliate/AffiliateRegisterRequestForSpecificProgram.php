<?php

namespace RelayWp\Affiliate\Pro\ValidationRequest\Affiliate;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;
use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\Core\Models\Program;

class AffiliateRegisterRequestForSpecificProgram implements FormRequest
{
    public function rules(Request $request)
    {
        $data = $request->all();

        $rules = [
            'program_id' => ['required', 'numeric'],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['is_affiliate_email_already_exists', 'required', 'email'],
        ];


        if (!empty($data['program_id'])) {
            $program_id = $data['program_id'];
            $program = Program::query()->find($program_id);

            $custom_affiliate_fields = Functions::jsonDecode($program->custom_affiliate_fields);
            $fields = $custom_affiliate_fields['fields'];

            foreach ($fields as $field) {
                if (($field['is_important'] ?? false) && !(in_array($field['field_name'], ['first_name', 'last_name', 'email']))) {
                    $rules[$field['field_name']] = ['required'];
                }
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            /* translators: placeholder description */
            'first_name.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('First Name', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'last_name.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Last Name', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'program_id.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Program', 'relay-affiliate-marketing')]),
            'program_id.numeric' => __('Expected value is numeric', 'relay-affiliate-marketing'),
            /* translators: placeholder description */
            'email.required' => vsprintf(esc_html__('%s is required', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'email.email' => vsprintf(esc_html__('Not a valid %s', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing')]),
            /* translators: placeholder description */
            'email.is_affiliate_email_already_exists' => vsprintf(esc_html__('%1$s already exists as %1$s', 'relay-affiliate-marketing'), [__('Email', 'relay-affiliate-marketing'), __('Affiliate', 'relay-affiliate-marketing')]),
        ];
    }
}
