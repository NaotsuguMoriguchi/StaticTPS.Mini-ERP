<?php

Route::group(['middleware' => ['web', 'authh', 'SetSessionData', 'auth', 'language', 'timezone', 'ContactSidebarMenu', 'CheckContactLogin'], 'prefix' => 'contact', 'namespace' => 'Modules\Crm\Http\Controllers'], function () {
    Route::resource('contact-dashboard', 'DashboardController');
    Route::get('contact-profile', 'ManageProfileController@getProfile');
    Route::post('contact-password-update', 'ManageProfileController@updatePassword');
    Route::post('contact-profile-update', 'ManageProfileController@updateProfile');
    Route::get('contact-purchases', 'PurchaseController@getPurchaseList');
    Route::get('contact-sells', 'SellController@getSellList');
    Route::get('contact-ledger', 'LedgerController@index');
    Route::get('contact-get-ledger', 'LedgerController@getLedger');
    Route::resource('bookings', 'ContactBookingController');
});

Route::group(['middleware' => ['web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'], 'namespace' => 'Modules\Crm\Http\Controllers', 'prefix' => 'crm'], function () {
    Route::get('all-contacts-login', 'ContactLoginController@allContactsLoginList');
    Route::resource('contact-login', 'ContactLoginController')->except(['show']);
    Route::resource('contact-schedule', 'ScheduleController');
    Route::get('contact-todays-schedule', 'ScheduleController@getTodaysSchedule');
    Route::get('lead-schedule', 'ScheduleController@getLeadSchedule');

    Route::resource('contact-schedule-log', 'ScheduleLogController');
    
    Route::get('install', 'InstallController@index');
    Route::post('install', 'InstallController@install');
    Route::get('install/uninstall', 'InstallController@uninstall');
    Route::get('install/update', 'InstallController@update');

    Route::resource('contact-leads', 'LeadController');
    Route::get('contact-leads/{id}/convert', 'LeadController@convertToCustomer');
    Route::get('contact-leads/{id}/post-life-stage', 'LeadController@postLifeStage');

    Route::get('{id}/send-campaign-notification', 'CampaignController@sendNotification');
    Route::resource('crm-campaigns', 'CampaignController');
    Route::get('dashboard', 'CrmDashboardController@index');
});
