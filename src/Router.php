<?php

/* CMS */
$app->any('/cms/', function () {
	return (new \Controllers\Dentarest())->handler();
});

$app->any('/cms/login/{site_id:[0-9]+}', function () {
    return (new \Controllers\Connection())->handler();
});

$app->get('/cms/logout/{site_id:[0-9]+}', function () {
    return (new \Controllers\Connection())->handler();
});

$app->any('/cms/element/',
    function(){
        return (new \Controllers\Elements())->handler();
    }
);

/* kyousei cms */
$app->any('/kyousei_cms/', function () {
    return (new \Controllers\Kyousei())->handler();
});

$app->any('/kyousei_cms/element/',
    function(){
        return (new \Controllers\Elements())->handler();
    }
);

$app->get('/kyousei_cms/recommend_list/', function () {
    return (new \Controllers\KyouseiRecommends())->handler();
});

$app->any('/kyousei_cms/recommend/{recommend_id:[0-9]+}', function () {
    return (new \Controllers\KyouseiRecommends())->handler();
});

$app->get('/kyousei_cms/case_list/', function () {
    return (new \Controllers\Cases())->handler();
});

$app->any('/kyousei_cms/case/{id:[0-9]+}', function () {
    return (new \Controllers\Cases())->handler();
});

$app->get('/kyousei_cms/delete_images/', function () {
    return (new \Controllers\KyouseiRecommends())->delete_images();
});

$app->get('/kyousei_cms/clinic_list/', function () {
    return (new \Controllers\Clinics())->handler();
});

$app->any('/kyousei_cms/clinic/{clinic_id:[0-9]+}', function () {
    return (new \Controllers\Clinics())->handler();
});

$app->any('/kyousei_cms/invisalign/{clinic_id:[0-9]+}', function () {
    return (new \Controllers\Invisalign())->handler();
});

$app->get('/kyousei_cms/tag_list/', function () {
    return (new \Controllers\Tags())->handler();
});

$app->any('/kyousei_cms/tag/{tag_id:[0-9]+}', function () {
    return (new \Controllers\Tags())->handler();
});

$app->get('/kyousei_cms/city_list/', function () {
    return (new \Controllers\Cities())->handler();
});

$app->any('/kyousei_cms/city/{city_id:[0-9]+}', function () {
    return (new \Controllers\Citiess())->handler();
});

$app->get('/kyousei_cms/profile_list/', function () {
    return (new \Controllers\Profiles())->handler();
});

$app->any('/kyousei_cms/profile/{doctor_id:[0-9]+}', function () {
    return (new \Controllers\Profiles())->handler();
});

$app->get('/kyousei_cms/station_list/', function () {
    return (new \Controllers\Stations())->handler();
});

$app->any('/kyousei_cms/station/{station_group_id:[0-9]+}', function () {
    return (new \Controllers\Stations())->handler();
});

$app->get('/kyousei_cms/internal_link/', function () {
    return (new \Controllers\InternalLinks())->handler();
});

$app->any('/kyousei_cms/internal_link/{section:[a-z_]+}', function () {
    return (new \Controllers\InternalLinks())->handler();
});

/* implant cms */

$app->any('/implant_cms/', function () {
    return (new \Controllers\Implant())->handler();
});

$app->any('/implant_cms/element/',
    function(){
        return (new \Controllers\Elements())->handler();
    }
);

$app->get('/implant_cms/recommend_list/', function () {
    return (new \Controllers\ImplantRecommends())->handler();
});

$app->any('/implant_cms/recommend/{recommend_id:[0-9]+}', function () {
    return (new \Controllers\ImplantRecommends())->handler();
});

$app->get('/implant_cms/case_list/', function () {
    return (new \Controllers\Cases())->handler();
});

$app->any('/implant_cms/case/{id:[0-9]+}', function () {
    return (new \Controllers\Cases())->handler();
});

$app->get('/implant_cms/delete_images/', function () {
    return (new \Controllers\ImplantRecommends())->delete_images();
});

$app->get('/implant_cms/clinic_list/', function () {
    return (new \Controllers\Clinics())->handler();
});

$app->any('/implant_cms/clinic/{clinic_id:[0-9]+}', function () {
    return (new \Controllers\Clinics())->handler();
});

$app->get('/implant_cms/tag_list/', function () {
    return (new \Controllers\Tags())->handler();
});

$app->any('/implant_cms/tag/{tag_id:[0-9]+}', function () {
    return (new \Controllers\Tags())->handler();
});

$app->get('/implant_cms/city_list/', function () {
    return (new \Controllers\Cities())->handler();
});

$app->any('/implant_cms/city/{city_id:[0-9]+}', function () {
    return (new \Controllers\Cities())->handler();
});

$app->get('/implant_cms/profile_list/', function () {
    return (new \Controllers\Profiles())->handler();
});

$app->any('/implant_cms/profile/{doctor_id:[0-9]+}', function () {
    return (new \Controllers\Profiles())->handler();
});

$app->get('/implant_cms/station_list/', function () {
    return (new \Controllers\Stations())->handler();
});

$app->any('/implant_cms/station/{station_group_id:[0-9]+}', function () {
    return (new \Controllers\Stations())->handler();
});

$app->get('/implant_cms/internal_link/', function () {
    return (new \Controllers\InternalLinks())->handler();
});

$app->any('/implant_cms/internal_link/{section:[a-z_]+}', function () {
    return (new \Controllers\InternalLinks())->handler();
});

/* shinbi cms */

$app->any('/shinbi_cms/', function () {
    return (new \Controllers\Shinbi())->handler();
});

$app->any('/shinbi_cms/element/',
    function(){
        return (new \Controllers\Elements())->handler();
    }
);

$app->get('/shinbi_cms/recommend_list/', function () {
    return (new \Controllers\ShinbiRecommends())->handler();
});

$app->any('/shinbi_cms/recommend/{recommend_id:[0-9]+}', function () {
    return (new \Controllers\ShinbiRecommends())->handler();
});

$app->get('/shinbi_cms/case_list/', function () {
    return (new \Controllers\Cases())->handler();
});

$app->any('/shinbi_cms/case/{id:[0-9]+}', function () {
    return (new \Controllers\Cases())->handler();
});

$app->get('/shinbi_cms/delete_images/', function () {
    return (new \Controllers\ShinbiRecommends())->delete_images();
});

$app->get('/shinbi_cms/clinic_list/', function () {
    return (new \Controllers\Clinics())->handler();
});

$app->any('/shinbi_cms/clinic/{clinic_id:[0-9]+}', function () {
    return (new \Controllers\Clinics())->handler();
});

$app->get('/shinbi_cms/tag_list/', function () {
    return (new \Controllers\Tags())->handler();
});

$app->any('/shinbi_cms/tag/{tag_id:[0-9]+}', function () {
    return (new \Controllers\Tags())->handler();
});

$app->get('/shinbi_cms/city_list/', function () {
    return (new \Controllers\Cities())->handler();
});

$app->any('/shinbi_cms/city/{city_id:[0-9]+}', function () {
    return (new \Controllers\Cities())->handler();
});

$app->get('/shinbi_cms/profile_list/', function () {
    return (new \Controllers\Profiles())->handler();
});

$app->any('/shinbi_cms/profile/{doctor_id:[0-9]+}', function () {
    return (new \Controllers\Profiles())->handler();
});

$app->get('/shinbi_cms/station_list/', function () {
    return (new \Controllers\Stations())->handler();
});

$app->any('/shinbi_cms/station/{station_group_id:[0-9]+}', function () {
    return (new \Controllers\Stations())->handler();
});

$app->get('/shinbi_cms/internal_link/', function () {
    return (new \Controllers\InternalLinks())->handler();
});

$app->any('/shinbi_cms/internal_link/{section:[a-z_]+}', function () {
    return (new \Controllers\InternalLinks())->handler();
});


// おすすめ歯科医院リスト抽出
$app->any('/cms/get_recommend/',
    function(){
        return (new \Controllers\GetRecommend())->handler();
    }
);

$app->any('/cms/get_implant_recommend/',
    function(){
        return (new \Controllers\GetImplantRecommend())->handler();
    }
);

$app->any('/cms/get_shinbi_recommend/',
    function(){
        return (new \Controllers\GetShinbiRecommend())->handler();
    }
);

// CSVデータインポート
$app->any('/cms/import_clinics/',
    function(){
        return (new \Controllers\ImportClinics())->handler();
    }
);

/* Portal */
$app->get('/',
    function () {
        return (new \Controllers\PortalTop())->handler();
    }
);

$app->get('/recommend/',
    function () {
        return (new \Controllers\Recommends())->handler();
    }
);

$app->get('/recommend/{pref_pathname:[a-z]+}/',
    function () {
        return (new \Controllers\Recommends())->handler();
    }
);

$app->get('/recommend/{pref_pathname:[a-z]+}/{city_pathname:[a-z0-9\-]+}/',
    function () {
        return (new \Controllers\Recommends())->handler();
    }
);

$app->get('/recommend/{pref_pathname:[a-z]+}/{city_pathname:[a-z0-9\-]+}/{attribute_pathname:[a-z0-9\-]+}/',
    function () {
        return (new \Controllers\Recommends())->handler();
    }
);

$app->get('/profile/',
    function () {
        return (new \Controllers\Profiles())->handler();
    }
);

$app->get('/profile/{doctor_en_name:[a-z0-9\-]+}/',
    function () {
        return (new \Controllers\Profiles())->handler();
    }
);

$app->get('/clinic/{clnic_id:[0-9]+}/case/{attribute_pathname:[a-z0-9\-]+}/',
    function () {
        return (new \Controllers\Cases())->handler();
    }
);

// Ajax用
$app->get('/parts/',
    function(){
        return (new \Controllers\Parts())->handler();
    }
);
// 404ページ
$app->any('/notfound',
	function() use ($app){
		$handler = $app->getContainer()->get('notFoundHandler');
		return $handler($app->getContainer()->get('request'), $app->getContainer()->get('response'));
	}
);
// サイトマップ
$app->any('/sitemap', function () {
	return (new \Controllers\Sitemap())->handler();
});
