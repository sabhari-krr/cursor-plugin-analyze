<?php

namespace RelayWp\Affiliate\Pro\Models;

use Cartrabbit\Request\Request;
use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\WC;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\Core\Models\Member;


defined("ABSPATH") or exit;

class Program
{
    public static function getConfirmationHTML($program, $affiliate)
    {
        $custom_fields = json_decode($program->custom_affiliate_fields, true);

        $confirmation = $custom_fields['confirmation_fields'] ?? [];
        $body_text = $confirmation['body_text'] ?? 'We have successfully captured your affiliate request and will contact you via email for further updates.';

        $end_point = WC::getAffilateEndPoint();
        $referral_url = Affiliate::getReferralCodeURL($affiliate);
        $program_name = $program->title;
        $short_codes = [
            '{{affiliate_dashboard}}' => "<a href='{$end_point}' target='_blank'>Affiliate Dashboard</a>",
            '{{affiliate_link}}' => "<a href='{$referral_url}' target='_blank'>{$referral_url}</a>",
            '{{program_name}}' => $program_name,
        ];

        $member = Member::query()->find($affiliate->member_id);

        $data = [
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
            'email' => $member->email,
            'program_name' => $program->title,
            'program_description' => $program->description
        ];

        $short_codes = apply_filters('rwpa_wprelay_pbaf_confirmation_short_codes', $short_codes, $data);

        foreach ($short_codes as $short_code => $short_code_value) {
            $body_text = str_replace($short_code, $short_code_value, $body_text);
        }

        $confirmation = [
            'header_text' => $confirmation['header_text'] ?? 'Affiliate Confirmation Request',
            'body_text' => $body_text,
            'icon_url' => $confirmation['icon_url'] ?? null,
            "container" => "wprelay-pbar-confirmation-container",
            "header_text_class" => "wprelay-pbar-confirmation-header",
            "body_text_class" => "wprelay-pbar-confirmation-body",
            "icon_url_class" => "wprelay-pbar-confirmation-icon",
        ];

        $path = RWPA_PLUGIN_PATH . 'Pro/views/';

        ob_start(); // Start output buffering
        include $path . 'pbar-confirmation.php'; // Include the PHP file
        return ob_get_clean();
    }

    public static function getCustomFieldsData($custom_affiliate_fields)
    {
        $custom_affiliate_fields = Functions::jsonDecode($custom_affiliate_fields);

        if (empty($custom_affiliate_fields)) return null;

        $overview = $custom_affiliate_fields['overview'] ?? [];
        $advanced_css = $custom_affiliate_fields['advanced_css'] ?? [];
        $fields = $custom_affiliate_fields['fields'] ?? [];

        $confirmation_fields = $custom_affiliate_fields['confirmation_fields'] ?? [];

        //construct the custom fields array and json encode
        return static::getCustomFieldsArray($overview, $advanced_css, $fields, $confirmation_fields);
    }

    private static function getCustomFieldsArray($overview, $advanced_css, $fields, $confirmation_fields)
    {

        $custom_fields = [
            'overview' => [
                'title' => $overview['title'] ?? '',
                'description' => $overview['description'] ?? '', //Functions::removeScripts($description),
                'button_text' => $overview['button_text'] ?? '',
            ],
            'fields' => array_map(function ($field) {
                return [
                    "type" => $field['type'] ?? '',
                    "label" => $field['label'] ?? '',
                    "field_name" => Functions::toSnakeCase($field['field_name']) ?? '',
                    "is_open" => (!in_array($field['field_name'], ['first_name', 'last_name', 'email']) && Functions::getBoolValue($field['is_open'] ?? false)),
                    "can_edit" => !in_array($field['field_name'], ['first_name', 'last_name', 'email']),
                    "is_default" => in_array($field['field_name'], ['first_name', 'last_name', 'email']),
                    "is_important" => in_array($field['field_name'], ['first_name', 'last_name', 'email']) || Functions::getBoolValue($field['is_important'] ?? false),
                ];
            }, $fields),
            'advanced_css' => [
                'enabled' => Functions::getBoolValue($advanced_css['enabled'] ?? false),
                'styles' => $advanced_css['styles'] ?? '',
            ],
            'confirmation_fields' => [
                'header_text' => $confirmation_fields['header_text'] ?? '',
                'body_text' => $confirmation_fields['body_text'] ?? '',
                'icon_url' => $confirmation_fields['icon_url'] ?? null,
            ]
        ];

        return $custom_fields;
    }

    public static function getFormDataForRendering($program)
    {
        $custom_fields = json_decode($program->custom_affiliate_fields, true);

        $fields = $custom_fields['fields'] ?? [];
        $overview = $custom_fields['overview'] ?? [];
        $confirmation = $custom_fields['confirmation_fields'] ?? [];
        $advanced_css = $custom_fields['advanced_css'] ?? [];

        $fields = array_filter($fields, function ($field) {
            return !(bool)$field['is_default'];
        });

        return [
            "content" => [
                "wrapper" => "wprelay-pbar-container",
                "container" => "wprelay-pbar-fields-container",
            ],
            "fields_container" => [
                "fields_wrapper" => "wprelay-pbar-input-fields-wrapper",
                "input_container_class" => "wprelay-pbar-input-container",
                'label_class' => "wprelay-pbar-label",
                'input_class' => "wprelay-pbar-input",
            ],
            'fields' => array_map(function ($field) {
                return [
                    'name' => $field['field_name'],
                    'id' => "wprelay-{$field['field_name']}",
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'is_important' => Functions::getBoolValue($field['is_important'] ?? false),
                    'label_class' => "wprelay-pbar-label wprelay_pbar_{$field['field_name']}_label",
                    'input_class' => "wprelay-pbar-input wprelay_pbar_{$field['field_name']}_input",
                ];
            }, $fields),
            'overview' => [
                'title' => $overview['title'],
                'description' => $overview['description'],
                'submit_text' => $overview['button_text'],
                "container" => "wprelay-pbar-overview-container",
                "overview_title_class" => "wprelay-pbar-overview-title",
                "overview_description_class" => "wprelay-pbar-overview-description",
                "button_container_class" => "wprelay-pbar-button-container",
                "button_class" => "wprelay-pbar-button",
            ],
            'advanced_css' => [
                'enabled' => (bool)$advanced_css['enabled'] ?? false,
                'styles' => $advanced_css['styles'] ?? '',
            ],
        ];
    }

    public static function getRegistrationPageShortCode($program)
    {
        return "[rwpa_wprelay_registration_with_custom_fields program_id='{$program->id}' slug='{$program->custom_field_shortcode}']";
    }

    public static function getCustomFieldDataForForm(Request $request)
    {
        $overview = $request->get('overview', []);

        $description = $request->get('overview.description', '', 'html') ?? '';

        $overview['description'] = str_replace('\"', "'", $description);

        $advanced_css = $request->get('advanced_css', []);
        $advanced_css['styles'] = $request->get('advanced_css.styles', '', 'html') ?? '';
        $fields = $request->get('fields', []);
        $confirmation_fields = $request->get('confirmation_fields', []);

        $body_text = $request->get('confirmation_fields.body_text', '', 'html') ?? '';
        $confirmation_fields['body_text'] = str_replace('\"', "'", $body_text);

        //construct the custom fields array and json encode
        return wp_json_encode(static::getCustomFieldsArray($overview, $advanced_css, $fields, $confirmation_fields));
    }
}
