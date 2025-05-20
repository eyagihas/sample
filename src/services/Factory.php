<?php

namespace Services;

class Factory
{
	protected static $_instance = [];

    public static function  get_instance($type, $sitename = '')
    {
		$result = null;
		if(!isset(self::$_instance[$type])){
			//インスタンス無し
			switch(true){
				case $type === 'site':
					$result = new \Models\MSites();
					break;
				case $type === 'region':
					$result = new \Models\MRegion();
					break;
				case $type === 'prefecture':
					$result = new \Models\MPrefectures();
					break;
				case $type === 'city':
					$result = new \Models\MCities();
					break;
				case $type === 'station':
					$result = new \Models\MStations();
					break;
				case $type === 'attribute':
					$result = new \Models\MAttributes();
					break;
				case $type === 'payment':
					$result = new \Models\MPayments();
					break;
				case $type === 'feature_type':
					$result = new \Models\MFeatureTypes();
					break;
				case $type === 'tag':
					$result = new \Models\MTags();
					break;
				case $type === 'account':
					$result = new \Models\TAccounts();
					break;
				case $type === 'log':
					$result = new \Models\TLogs();
					break;
				case $type === 'clinic':
					$result = new \Models\TClinics();
					break;
				case $type === 'clinic_operation_time':
					$result = new \Models\TClinicOperationTimes();
					break;
				case $type === 'clinic_image':
					$result = new \Models\TClinicImages($sitename);
					break;
				case $type === 'case_image':
					$result = new \Models\TCaseImages($sitename);
					break;
				case $type === 'profile':
					$result = new \Models\TProfiles();
					break;
				case $type === 'profile_clinic':
					$result = new \Models\TProfileClinics();
					break;
				case $type === 'profile_career':
					$result = new \Models\TProfileCareers();
					break;
				case $type === 'profile_image':
					$result = new \Models\TProfileImages();
					break;
				case $type === 'profile_qualification':
					$result = new \Models\TProfileQualifications();
					break;
				case $type === 'kyousei_clinic':
					$result = new \Models\TKyouseiClinics();
					break;
				case $type === 'kyousei_operation_time':
					$result = new \Models\TKyouseiOperationTimes();
					break;
				case $type === 'kyousei_recommend':
					$result = new \Models\TKyouseiRecommends();
					break;
				case $type === 'kyousei_recommend_image':
					$result = new \Models\TKyouseiRecommendImages();
					break;
				case $type === 'kyousei_recommend_clinic':
					$result = new \Models\TKyouseiRecommendClinics();
					break;
				case $type === 'kyousei_recommend_clinic_feature':
					$result = new \Models\TKyouseiRecommendClinicFeatures();
					break;
				case $type === 'kyousei_recommend_clinic_fee':
					$result = new \Models\TKyouseiRecommendClinicFees();
					break;
				case $type === 'kyousei_recommend_clinic_flow':
					$result = new \Models\TKyouseiRecommendClinicFlows();
					break;
				case $type === 'kyousei_case':
					$result = new \Models\TKyouseiCases();
					break;
				case $type === 'kyousei_explanation':
					$result = new \Models\TKyouseiExplanations();
					break;
				case $type === 'kyousei_self_clinic':
					$result = new \Models\TKyouseiSelfClinics();
					break;
				case $type === 'kyousei_self_operation_time':
					$result = new \Models\TKyouseiSelfOperationTimes();
					break;
				case $type === 'kyousei_self_payment':
					$result = new \Models\TKyouseiSelfPayments();
					break;
				case $type === 'kyousei_self_invisalign_fee':
					$result = new \Models\TKyouseiSelfInvisalignFee();
					break;
				case $type === 'kyousei_self_invisalign_flow':
					$result = new \Models\TKyouseiSelfInvisalignFlows();
					break;
				case $type === 'kyousei_self_clinic_feature':
					$result = new \Models\TKyouseiSelfClinicFeatures();
					break;
				case $type === 'implant_clinic':
					$result = new \Models\TImplantClinics();
					break;
				case $type === 'implant_operation_time':
					$result = new \Models\TImplantOperationTimes();
					break;
				case $type === 'implant_recommend':
					$result = new \Models\TImplantRecommends();
					break;
				case $type === 'implant_recommend_image':
					$result = new \Models\TImplantRecommendImages();
					break;
				case $type === 'implant_recommend_clinic':
					$result = new \Models\TImplantRecommendClinics();
					break;
				case $type === 'implant_recommend_clinic_feature':
					$result = new \Models\TImplantRecommendClinicFeatures();
					break;
				case $type === 'implant_recommend_clinic_fee':
					$result = new \Models\TImplantRecommendClinicFees();
					break;
				case $type === 'implant_recommend_clinic_flow':
					$result = new \Models\TImplantRecommendClinicFlows();
					break;
				case $type === 'implant_case':
					$result = new \Models\TImplantCases();
					break;
				case $type === 'implant_explanation':
					$result = new \Models\TImplantExplanations();
					break;
				case $type === 'shinbi_clinic':
					$result = new \Models\TShinbiClinics();
					break;
				case $type === 'shinbi_operation_time':
					$result = new \Models\TShinbiOperationTimes();
					break;
				case $type === 'shinbi_recommend':
					$result = new \Models\TShinbiRecommends();
					break;
				case $type === 'shinbi_recommend_image':
					$result = new \Models\TShinbiRecommendImages();
					break;
				case $type === 'shinbi_recommend_clinic':
					$result = new \Models\TShinbiRecommendClinics();
					break;
				case $type === 'shinbi_recommend_clinic_feature':
					$result = new \Models\TShinbiRecommendClinicFeatures();
					break;
				case $type === 'shinbi_recommend_clinic_fee':
					$result = new \Models\TShinbiRecommendClinicFees();
					break;
				case $type === 'shinbi_recommend_clinic_flow':
					$result = new \Models\TShinbiRecommendClinicFlows();
					break;
				case $type === 'shinbi_case':
					$result = new \Models\TShinbiCases();
					break;
				case $type === 'shinbi_explanation':
					$result = new \Models\TShinbiExplanations();
					break;
				case $type === 'recommend_tag':
					$result = new \Models\TRecommendTags($sitename);
					break;
				case $type === 'csv_clinic':
					$result = new \Models\TCsvClinics($sitename);
					break;
				case $type === 'site_clinic':
					$result = new \Models\TSiteClinics($sitename);
					break;
				case $type === 'top_city_station':
					$result = new \Models\TTopCityStation($sitename);
					break;
				case $type === 'top_recommend':
					$result = new \Models\TTopRecommends($sitename);
					break;

				/* portal */
				case $type === 'recommend':
					$result = new \Models\TRecommends();
					break;
			}
			if($result !== null){
				self::$_instance[$type] = $result;
			}
		}else{
			//インスタンスがある場合は配列から取得
			$result = self::$_instance[$type];
		}

        return $result;
    }
}
