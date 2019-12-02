<?php

namespace Abs\WarrantyPolicyPkg;

use App\Company;
use App\Config;
use Illuminate\Database\Eloquent\Model;

class WarrantyPolicyDetail extends Model {
	protected $table = 'warranty_policy_details';
	public $timestamps = false;
	protected $fillable = [
		'warranty_policy_id',
		'warranty_type_id',
		'duration_type_id',
		'duration',
		'more_info',
		'priority',
	];

	public function warrantyPolicy() {
		return $this->belongsTo('Abs\WarrantyPolicyPkg\WarrantyPolicy', 'warranty_policy_id', 'id');
	}

	public static function createFromObject($record_data) {

		$errors = [];
		$company = Company::where('code', $record_data->company)->first();
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company);
			return;
		}

		$admin = $company->admin();
		if (!$admin) {
			dump('Default Admin user not found');
			return;
		}

		$type = Config::where('name', $record_data->type)->where('config_type_id', 89)->first();
		if (!$type) {
			$errors[] = 'Invalid Tax Type : ' . $record_data->type;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->tax_name,
		]);
		$record->type_id = $type->id;
		$record->created_by_id = $admin->id;
		$record->save();
		return $record;
	}

	public static function createFromCollection($records) {
		foreach ($records as $key => $record_data) {
			try {
				if (!$record_data->company) {
					continue;
				}
				$record = self::createFromObject($record_data);
			} catch (Exception $e) {
				dd($e);
			}
		}
	}

}
