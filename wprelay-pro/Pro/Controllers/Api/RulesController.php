<?php

namespace RelayWp\Affiliate\Pro\Controllers\Api;

defined("ABSPATH") or exit;

use Cartrabbit\Request\Request;
use Cartrabbit\Request\Response;
use Error;
use Exception;
use RelayWp\Affiliate\App\Helpers\Functions;
use RelayWp\Affiliate\App\Helpers\PluginHelper;
use RelayWp\Affiliate\App\Services\Database;
use RelayWp\Affiliate\Core\Models\Affiliate;
use RelayWp\Affiliate\Core\Models\Member;
use RelayWp\Affiliate\Core\Models\Program;
use RelayWp\Affiliate\Pro\Models\Rules;
use RelayWp\Affiliate\Pro\Models\RulesMeta;
use RelayWp\Affiliate\Pro\Resources\Rules\RulesCollection;
use RelayWp\Affiliate\Pro\ValidationRequest\Rules\RuleDeleteRequest;
use RelayWp\Affiliate\Pro\ValidationRequest\Rules\RulesRequest;
use RelayWp\Affiliate\Pro\ValidationRequest\Rules\RuleUpdateStatusRequest;

class RulesController
{

    public function index(Request $request)
    {
        try {
            //code
            $program_id = $request->get('program_id');

            $rulesTable = Rules::getTableName();
            $rulesMetaTable = RulesMeta::getTableName();
            $affiliateTable = Affiliate::getTableName();
            $memberTable = Member::getTableName();

            $program = Program::query()->findOrFail($program_id);

            $query = Rules::query()
                ->select("{$rulesTable}.id as rule_id, title, {$rulesTable}.description as description, start_date, end_date, commission_data, {$rulesTable}.status as status, {$rulesTable}.program_id, affiliates_type, GROUP_CONCAT({$affiliateTable}.id SEPARATOR ',') as affiliate_ids")
                ->leftJoin($rulesMetaTable, "{$rulesMetaTable}.rule_id = {$rulesTable}.id AND {$rulesMetaTable}.type = 'affiliate'")
                ->leftJoin($affiliateTable, "{$rulesMetaTable}.model_id = {$affiliateTable}.id")
                ->where("{$rulesTable}.deleted_at is null")
                ->where("{$rulesTable}.program_id = %d", [$program->id])
                ->groupBy("{$rulesTable}.id")
                ->orderBy("{$rulesTable}.id", "DESC");

            $rules = $query->get();

            $affiliate_ids = [];
            foreach ($rules as $rule) {
                $ids = $rule->affiliate_ids;
                if (empty($ids)) continue;
                $current_affiliate_ids = explode(',', $ids);
                foreach ($current_affiliate_ids as $id) {
                    $affiliate_ids[] = $id;
                }
            }

            if (empty($affiliate_ids)) {
                $affiliates = [];
            } else {
                $affiliate_ids_as_string = implode(", ", $affiliate_ids);
                $affiliate_query = Affiliate::query()
                    ->select("{$affiliateTable}.id as affiliate_id, {$memberTable}.id as member_id, {$memberTable}.email as affiliate_email")
                    ->leftJoin($memberTable, "{$memberTable}.id = {$affiliateTable}.member_id")
                    ->where("{$affiliateTable}.id IN (" . $affiliate_ids_as_string . ")");
                $affiliates = $affiliate_query->get();
            }

            foreach ($rules as &$rule) {
                $rule->affiliates = [];
                $ids = $rule->affiliate_ids;
                if (empty($ids)) continue;
                $current_affiliate_ids = explode(',', $ids);
                foreach ($affiliates as $affiliate) {
                    if (in_array($affiliate->affiliate_id, $current_affiliate_ids)) {
                        $rule->affiliates[] = $affiliate;
                    }
                }
            }

            return RulesCollection::collection([$program, $rules]);
        } catch (Exception | Error $exception) {
            PluginHelper::logError('Error Occurred While Processing', [__CLASS__, __FUNCTION__], $exception);
            return Response::error(PluginHelper::serverErrorMessage());
        }
    }

    public function create(Request $request)
    {
        $request->validate(new RulesRequest());

        try {
            Database::beginTransaction();


            $title = $request->get('title');
            $description = $request->get('description');
            $affiliates_type = $request->get('affiliates_type');
            $program_id = $request->get('program_id');

            $start_date = Functions::formatDate($request->get('start_date', null));
            $end_date = Functions::formatDate($request->get('end_date', null));

            $start_date = empty($start_date) ? null : Functions::wpToUTCTime($start_date);
            $end_date = empty($end_date) ? null : Functions::wpToUTCTime($end_date);

            $created_at = Functions::currentUTCTime();
            $updated_at = Functions::currentUTCTime();

            Rules::query()->create([
                'title' => $title,
                'description' => $description,
                'status' => 'active',
                'program_id' => $program_id,
                'commission_data' => Rules::getCommissionDataFromRequest($request),
                'affiliates_type' => $affiliates_type,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ]);

            $last_inserted_rules_id = Rules::query()->lastInsertedId();

            if ($affiliates_type == 'specific') {
                RulesMeta::syncAffiliates($last_inserted_rules_id, $request);
            }

            Database::commit();

            Response::success([
                'message' => __('Rule Created Successfully', 'relay-affiliate-marketing'),
                'rule_id' => $last_inserted_rules_id,
            ]);
        } catch (Exception | Error $exception) {
            Database::rollBack();
            PluginHelper::logError('Error Occurred While Creating Program', [__CLASS__, __FUNCTION__], $exception);
            return Response::error(PluginHelper::serverErrorMessage());
        }
    }

    public function update(Request $request)
    {
        $request->validate(new RulesRequest());
        try {
            Database::beginTransaction();

            $title = $request->get('title');
            $description = $request->get('description');
            $affiliates_type = $request->get('affiliates_type');
            $program_id = $request->get('program_id');

            $start_date = Functions::formatDate($request->get('start_date', null));
            $end_date = Functions::formatDate($request->get('end_date', null));

            $start_date = empty($start_date) ? null : Functions::wpToUTCTime($start_date);
            $end_date = empty($end_date) ? null : Functions::wpToUTCTime($end_date);
            $updated_at = Functions::currentUTCTime();
            $rule_id = $request->get('rule_id');
            $rule = Rules::query()->findOrFail($rule_id);

            Rules::query()->update([
                'title' => $title,
                'description' => $description,
                'status' => 'active',
                'program_id' => $program_id,
                'commission_data' => Rules::getCommissionDataFromRequest($request),
                'affiliates_type' => $affiliates_type,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'updated_at' => $updated_at,
            ], ['id' => $rule->id]);

            RulesMeta::query()->delete([
                'rule_id' => $rule->id
            ]);

            if ($affiliates_type == 'specific') {
                RulesMeta::syncAffiliates($rule->id, $request);
            }
            Database::commit();

            Response::success(['message' => __('Rule Updated Successfully', 'relay-affiliate-marketing')]);
        } catch (Exception | Error $exception) {
            Database::rollBack();
            PluginHelper::logError('Error Occurred While Creating Program', [__CLASS__, __FUNCTION__], $exception);
            return Response::error(PluginHelper::serverErrorMessage());
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate(new RuleUpdateStatusRequest());

        try {
            $rule_id = $request->get('rule_id');
            $status = $request->get('status');
            $rule = Rules::query()->findOrFail($rule_id);

            $status = $status == Rules::ACTIVE ? Rules::ACTIVE : Rules::DRAFT;

            Rules::query()->update([
                'status' => $status
            ], ['id' => $rule->id]);

            Response::success([
                'message' => __('Rule Status Updated Successfully', 'relay-affiliate-marketing')
            ]);
        } catch (Exception | Error $exception) {
            PluginHelper::logError('Error Occurred While Updating Rule Status', [__CLASS__, __FUNCTION__], $exception);
            return Response::error(PluginHelper::serverErrorMessage());
        }
    }

    public function destroy(Request $request)
    {
        $request->validate(new RuleDeleteRequest());

        try {
            $rule_id = $request->get('rule_id');
            $rule = Rules::query()->findOrFail($rule_id);

            Rules::query()->update([
                'deleted_at' => Functions::currentUTCTime()
            ], ['id' => $rule->id]);

            Response::success([
                'message' => __('Rule Deleted Successfully', 'relay-affiliate-marketing')
            ]);
        } catch (Exception | Error $exception) {
            PluginHelper::logError('Error Occurred While Creating Program', [__CLASS__, __FUNCTION__], $exception);
            return Response::error(PluginHelper::serverErrorMessage());
        }
    }
}
