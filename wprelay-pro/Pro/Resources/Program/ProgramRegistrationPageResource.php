<?php

namespace RelayWp\Affiliate\Pro\Resources\Program;

defined("ABSPATH") or exit;

use RelayWp\Affiliate\App\Traits\Conditionable;
use RelayWp\Affiliate\App\Resource;
use RelayWp\Affiliate\Pro\Models\Program;

class ProgramRegistrationPageResource extends Resource
{
    use Conditionable;

    public function toArray($program)
    {
        return [
            "program_id" => $program->id,
            "title" => $program->title,
            "description" => $program->description,
            "status" => $program->status,
            "is_active" => $program->status == 'active',
            "auto_approve" => (bool)$program->auto_approve,
            "is_already_created" => (bool)$program->custom_field_shortcode,
            "custom_field_shortcode" => $program->custom_field_shortcode ? Program::getRegistrationPageShortCode($program) : null,
            "custom_fields" => Program::getCustomFieldsData($program->custom_affiliate_fields),
        ];
    }
}
