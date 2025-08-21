<?php

namespace RelayWp\Affiliate\Pro\ValidationRequest;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Validation\FormRequest;
use RelayWp\Affiliate\App\Helpers\Functions;

class CreateProgramPageRequest implements FormRequest
{
    public $request_data = [];

    public function rules(Request $request)
    {
        $data = $request->all();

        $this->request_data = $data;

        $rules = [
            'program_id' => ['required', 'numeric'],
            'overview' => ['required', 'array'],
            'fields' => ['required', 'array'],
            'advanced_css' => ['required', 'array'],
            'confirmation_fields' => ['required', 'array'],
        ];

        if (isset($data['overview'])) {
            $rules['overview.title'] = ['required'];
            $rules['overview.description'] = ['required'];
            $rules['overview.button_text'] = ['required'];
        }

        if (isset($data['fields'])) {
            $fields = $data['fields'];

            foreach ($fields as $index => $field) {
                $rules["fields.{$index}"] = ['required', 'array'];
                $rules["fields.{$index}.type"] = ['required'];
                $rules["fields.{$index}.label"] = ['required'];
                $rules["fields.{$index}.field_name"] = ['required'];
                $rules["fields.{$index}.is_open"] = ['optional', 'bool_type'];
                $rules["fields.{$index}.can_edit"] = ['optional'];
                $rules["fields.{$index}.is_default"] = ['optional'];
            }
        }

        if (isset($data['advanced_css']) && Functions::getBoolValue($data['advanced_css']['enabled'])) {
            $rules["advanced_css.enabled"] = ['optional', 'bool_type'];
            if ($data['advanced_css']['enabled']) {
                $rules["advanced_css.styles"] = ['required'];
            }
        }

        if (isset($data['confirmation_fields'])) {
            $rules["confirmation_fields.header_text"] = ['required'];
            $rules["confirmation_fields.body_text"] = ['required'];
            $rules["confirmation_fields.icon_url"] = ['optional', 'url'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $data = $this->request_data;

        $messages = [
            //overview
            'overview.title.required' => __('Title is required', 'relay-affiliate-marketing'),
            'overview.description.required' => __('Description is required', 'relay-affiliate-marketing'),
            'overview.button_text.required' => __('Button text is required', 'relay-affiliate-marketing'),
        ];

        if (isset($data['fields'])) {
            $fields = $data['fields'];

            foreach ($fields as $index => $field) {
                $messages["fields.{$index}.required"] = __('Fields is required', 'relay-affiliate-marketing');
                $messages["fields.{$index}.type.required"] = __('Field type is required', 'relay-affiliate-marketing');
                $messages["fields.{$index}.label.required"] = __('Label name is required', 'relay-affiliate-marketing');
                $messages["fields.{$index}.field_name.required"] = __('Field name is required', 'relay-affiliate-marketing');
            }
        }

        if (isset($data['advanced_css'])) {
            $messages["advanced_css.enabled"] = ['optional', 'bool_type'];
            if (!empty($data['advanced_css']['enabled'])) {
                $messages["advanced_css.styles.required"] = __('Css style is required', 'relay-affiliate-marketing');
            }
        }

        if (isset($data['confirmation_fields'])) {
            $messages["confirmation_fields.header_text.required"] = __("Header text is required", 'relay-affiliate-marketing');
            $messages["confirmation_fields.body_text.required"] = __("Body text is required", 'relay-affiliate-marketing');
            $messages["confirmation_fields.icon_url.url"] = __("Url is not valid", 'relay-affiliate-marketing');
        }

        return $messages;
    }
}
