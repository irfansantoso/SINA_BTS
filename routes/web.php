<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Homepage\DashboardSinaController;
use App\Http\Controllers\Master\CompanySinaController;
use App\Http\Controllers\Master\SiteSinaController;
use App\Http\Controllers\Master\AccountTypeSinaController;
use App\Http\Controllers\Master\CurrencySinaController;
use App\Http\Controllers\Gl\AccountingPeriodSinaController;
use App\Http\Controllers\Tables\AccountListSinaController;
use App\Http\Controllers\Tables\DivisionListSinaController;
use App\Http\Controllers\Tables\CostListSinaController;
use App\Http\Controllers\Tables\JournalGroupSinaController;
use App\Http\Controllers\Tables\JournalSourceCodeSinaController;
use App\Http\Controllers\Forms\JournalSinaController;
use App\Http\Controllers\Reporting\RptGenLedSinaController;
use App\Http\Controllers\Reporting\RptTrBalanceSinaController;
use App\Http\Controllers\Email\ContractController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Route::get('view', function () {
//     return view('view', ['title' => '']);
// })->name('view');
Route::get('/', function () {
    return view('login', ['title' => '']);
})->name('login');
Route::get('login', [UserController::class, 'login_action'])->name('login.action');
Route::post('login', [UserController::class, 'login_action'])->name('login.action');
Route::get('register',[UserController::class, 'register'])->name('register');
Route::post('register', [UserController::class, 'register_action'])->name('register.action');

Route::get('users',[UserController::class, 'users'])->name('users')->middleware('auth');
Route::post('users', [UserController::class, 'users_add'])->name('users.add')->middleware('auth');
Route::get('users/json', [UserController::class, 'users_data'])->name('users.data')->middleware('auth');
Route::get('users/reset/{id}', [UserController::class, 'users_reset'])->name('users.reset');
Route::get('users/{id}', [UserController::class, 'edit'])->name('users.edit');
Route::put('users/update/{id}', [UserController::class, 'update'])->name('users.update');

Route::get('profile',[UserController::class, 'profile'])->name('profile')->middleware('auth');
Route::post('profile', [UserController::class, 'profile_edit'])->name('profile.edit')->middleware('auth');

Route::get('dashboard', [DashboardSinaController::class, 'dashboard'])->name('dashboard')->middleware('auth');

// Master
Route::get('companySina',[CompanySinaController::class, 'companySina_browse'])->name('companySina')->middleware('auth');
Route::get('companySina/json', [CompanySinaController::class, 'companySina_data'])->name('companySina.data')->middleware('auth');
Route::post('companySina', [CompanySinaController::class, 'companySina_add'])->name('companySina.add')->middleware('auth');
Route::get('companySina/{id_company}', [CompanySinaController::class, 'companySina_edit'])->name('companySina.edit');
Route::put('companySina/update/{id_company}', [CompanySinaController::class, 'companySina_update'])->name('companySina.update');
Route::delete('companySina/delete/{id_company}', [CompanySinaController::class, 'companySina_delete'])->name('companySina.delete');

Route::get('siteSina',[SiteSinaController::class, 'site_browse'])->name('siteSina')->middleware('auth');
Route::post('siteSina', [SiteSinaController::class, 'site_add'])->name('siteSina.add')->middleware('auth');

Route::get('accountTypeSina',[AccountTypeSinaController::class, 'accountTypeSina_browse'])->name('accountTypeSina')->middleware('auth');
Route::get('accountTypeSina/json', [AccountTypeSinaController::class, 'accountTypeSina_data'])->name('accountTypeSina.data')->middleware('auth');
Route::post('accountTypeSina', [AccountTypeSinaController::class, 'accountTypeSina_add'])->name('accountTypeSina.add')->middleware('auth');
Route::get('accountTypeSina/{id}', [AccountTypeSinaController::class, 'accountTypeSina_edit'])->name('accountTypeSina.edit');
Route::put('accountTypeSina/update/{id}', [AccountTypeSinaController::class, 'accountTypeSina_update'])->name('accountTypeSina.update');
Route::delete('accountTypeSina/delete/{id}', [AccountTypeSinaController::class, 'accountTypeSina_delete'])->name('accountTypeSina.delete');

Route::get('currencySina',[CurrencySinaController::class, 'currencySina_browse'])->name('currencySina')->middleware('auth');
Route::get('currencySina/json', [CurrencySinaController::class, 'currencySina_data'])->name('currencySina.data')->middleware('auth');
Route::post('currencySina', [CurrencySinaController::class, 'currencySina_add'])->name('currencySina.add')->middleware('auth');
Route::get('currencySina/{id}', [CurrencySinaController::class, 'currencySina_edit'])->name('currencySina.edit');
Route::put('currencySina/update/{id}', [CurrencySinaController::class, 'currencySina_update'])->name('currencySina.update');
Route::delete('currencySina/delete/{id}', [CurrencySinaController::class, 'currencySina_delete'])->name('currencySina.delete');

//TABLES

Route::get('accountListSina',[AccountListSinaController::class, 'accountListSina_browse'])->name('accountListSina')->middleware('auth');
Route::get('accountListSina/json', [AccountListSinaController::class, 'accountListSina_data'])->name('accountListSina.data')->middleware('auth');
Route::post('accountListSina', [AccountListSinaController::class, 'accountListSina_add'])->name('accountListSina.add')->middleware('auth');
Route::get('accountListSina/{id_acc_list}', [AccountListSinaController::class, 'accountListSina_edit'])->name('accountListSina.edit');
Route::put('accountListSina/update/{id_acc_list}', [AccountListSinaController::class, 'accountListSina_update'])->name('accountListSina.update');
Route::delete('accountListSina/delete/{id_acc_list}', [AccountListSinaController::class, 'accountListSina_delete'])->name('accountListSina.delete');

Route::get('divisionListSina',[DivisionListSinaController::class, 'divisionListSina_browse'])->name('divisionListSina')->middleware('auth');
Route::get('divisionListSina/json', [DivisionListSinaController::class, 'divisionListSina_data'])->name('divisionListSina.data')->middleware('auth');
Route::post('divisionListSina', [DivisionListSinaController::class, 'divisionListSina_add'])->name('divisionListSina.add')->middleware('auth');
Route::get('divisionListSina/{id_division}', [DivisionListSinaController::class, 'divisionListSina_edit'])->name('divisionListSina.edit');
Route::put('divisionListSina/update/{id_division}', [DivisionListSinaController::class, 'divisionListSina_update'])->name('divisionListSina.update');
Route::delete('divisionListSina/delete/{id_division}', [DivisionListSinaController::class, 'divisionListSina_delete'])->name('divisionListSina.delete');

Route::get('costListSina',[CostListSinaController::class, 'costListSina_browse'])->name('costListSina')->middleware('auth');
Route::get('costListSina/json', [CostListSinaController::class, 'costListSina_data'])->name('costListSina.data')->middleware('auth');
Route::post('costListSina', [CostListSinaController::class, 'costListSina_add'])->name('costListSina.add')->middleware('auth');
Route::get('costListSina/{id_cost}', [CostListSinaController::class, 'costListSina_edit'])->name('costListSina.edit');
Route::put('costListSina/update/{id_cost}', [CostListSinaController::class, 'costListSina_update'])->name('costListSina.update');
Route::delete('costListSina/delete/{id_cost}', [CostListSinaController::class, 'costListSina_delete'])->name('costListSina.delete');

Route::get('journalGroupSina',[JournalGroupSinaController::class, 'journalGroupSina_browse'])->name('journalGroupSina')->middleware('auth');
Route::get('journalGroupSina/json', [JournalGroupSinaController::class, 'journalGroupSina_data'])->name('journalGroupSina.data')->middleware('auth');
Route::post('journalGroupSina', [JournalGroupSinaController::class, 'journalGroupSina_add'])->name('journalGroupSina.add')->middleware('auth');
Route::get('journalGroupSina/{id_jgr}', [JournalGroupSinaController::class, 'journalGroupSina_edit'])->name('journalGroupSina.edit');
Route::put('journalGroupSina/update/{id_jgr}', [JournalGroupSinaController::class, 'journalGroupSina_update'])->name('journalGroupSina.update');
Route::delete('journalGroupSina/delete/{id_jgr}', [JournalGroupSinaController::class, 'journalGroupSina_delete'])->name('journalGroupSina.delete');

Route::get('journalSourceCodeSina',[JournalSourceCodeSinaController::class, 'journalSourceCodeSina_browse'])->name('journalSourceCodeSina')->middleware('auth');
Route::get('journalSourceCodeSina/json', [JournalSourceCodeSinaController::class, 'journalSourceCodeSina_data'])->name('journalSourceCodeSina.data')->middleware('auth');
Route::post('journalSourceCodeSina', [JournalSourceCodeSinaController::class, 'journalSourceCodeSina_add'])->name('journalSourceCodeSina.add')->middleware('auth');
Route::get('journalSourceCodeSina/cjgr/{c_jgr}', [JournalSourceCodeSinaController::class, 'journalSourceCodeSina_cjgr'])->name('journalSourceCodeSina.cjgr');
Route::get('journalSourceCodeSina/{id_jsc}', [JournalSourceCodeSinaController::class, 'journalSourceCodeSina_edit'])->name('journalSourceCodeSina.edit');
Route::put('journalSourceCodeSina/update/{id_jsc}', [JournalSourceCodeSinaController::class, 'journalSourceCodeSina_update'])->name('journalSourceCodeSina.update');
Route::delete('journalSourceCodeSina/delete/{id_jsc}', [JournalSourceCodeSinaController::class, 'journalSourceCodeSina_delete'])->name('journalSourceCodeSina.delete');

//GENERAL LEDGER

Route::get('journalSina',[JournalSinaController::class, 'journalSina_browse'])->name('journalSina')->middleware('auth');
Route::post('journalSina', [JournalSinaController::class, 'journalSina_add'])->name('journalSina.add')->middleware('auth');
Route::get('journalSina/cjgr/{c_jgr}', [JournalSinaController::class, 'journalSina_cjgr'])->name('journalSina.cjgr');
Route::get('journalSina/jsc/{c_jgr}', [JournalSinaController::class, 'journalSina_jsc'])->name('journalSina.jsc');
Route::get('journalSina/jsrNo/{c_jgr}/{c_jrc}', [JournalSinaController::class, 'journalSina_jsrNo'])->name('journalSina.jsrNo');
Route::get('journalSina/setFormByHeader/{j_jrc_no}/{c_jgr}/{c_jrc}', [JournalSinaController::class, 'journalSina_setFormByHeader'])->name('journalSina.setFormByHeader');
Route::get('journalSina/{id_jsc}', [JournalSinaController::class, 'journalSina_edit'])->name('journalSina.edit');
Route::put('journalSina/update/{j_jrc_no}/{c_jgr}/{c_jrc}', [JournalSinaController::class, 'journalSina_update'])->name('journalSina.update');


Route::post('journalDetailSina', [JournalSinaController::class, 'journalDetailSina_add'])->name('journalDetailSina.add')->middleware('auth');
Route::get('journalDetailSina/json', [JournalSinaController::class, 'journalDetailSina_data'])->name('journalDetailSina.data')->middleware('auth');
Route::get('journalDetailSina/setDebKre/{j_jrc_no}/{c_jgr}/{c_jrc}', [JournalSinaController::class, 'journalDetailSina_setDebKre'])->name('journalDetailSina.setDebKre');
Route::get('journalDetailSina/{id_jd}', [JournalSinaController::class, 'journalDetailSina_edit'])->name('journalDetailSina.edit');
Route::put('journalDetailSina/update/{id_jd}', [JournalSinaController::class, 'journalDetailSina_update'])->name('journalDetailSina.update');
Route::delete('journalDetailSina/delete/{id_jd}', [JournalSinaController::class, 'journalDetailSina_delete'])->name('journalDetailSina.delete');

Route::get('accountingPeriodSina',[AccountingPeriodSinaController::class, 'accountingPeriodSina_browse'])->name('accountingPeriodSina')->middleware('auth');
Route::get('accountingPeriodSina/json', [AccountingPeriodSinaController::class, 'accountingPeriodSina_data'])->name('accountingPeriodSina.data')->middleware('auth');
Route::post('accountingPeriodSina', [AccountingPeriodSinaController::class, 'accountingPeriodSina_add'])->name('accountingPeriodSina.add')->middleware('auth');
Route::get('accountingPeriodSina/{id_period}', [AccountingPeriodSinaController::class, 'accountingPeriodSina_edit'])->name('accountingPeriodSina.edit');
Route::put('accountingPeriodSina/update/{id_period}', [AccountingPeriodSinaController::class, 'accountingPeriodSina_update'])->name('accountingPeriodSina.update');
Route::put('accountingPeriodSina/updateStatus/{id_period}', [AccountingPeriodSinaController::class, 'accountingPeriodSina_updateStatus'])->name('accountingPeriodSina.updateStatus');
Route::delete('accountingPeriodSina/delete/{id_period}', [AccountingPeriodSinaController::class, 'accountingPeriodSina_delete'])->name('accountingPeriodSina.delete');

// Reporting

Route::get('rptGenLedSina',[RptGenLedSinaController::class, 'rptGenLedSina_browse'])->name('rptGenLedSina')->middleware('auth');
Route::get('rptGenLedSina/setPeriode/{month}/{year}', [RptGenLedSinaController::class, 'rptGenLedSina_setPeriode'])->name('rptGenLedSina.setPeriode');
Route::get('rptGenLedSinaModal/{s_date}/{e_date}/{acc_no}/{acc_no_end}/{code_cost}/{code_div}', [RptGenLedSinaController::class, 'rptGenLedSina_modal'])->name('rptGenLedSinaModal')->middleware('auth');
Route::get('rptGenLedSinaXls/{s_date}/{e_date}/{acc_no}/{acc_no_end}/{code_cost}/{code_div}', [RptGenLedSinaController::class, 'rptGenLedSina_xls'])->name('rptGenLedSinaXls')->middleware('auth');

Route::get('rptTrBalanceSina',[RptTrBalanceSinaController::class, 'rptTrBalanceSina_browse'])->name('rptTrBalanceSina')->middleware('auth');
Route::get('rptTrBalanceSina/setPeriode/{month}/{year}', [RptTrBalanceSinaController::class, 'rptTrBalanceSina_setPeriode'])->name('rptTrBalanceSina.setPeriode');
Route::get('rptTrBalanceSinaModal/{m_date}/{y_date}/{acc_no}/{acc_no_end}/{code_div}', [RptTrBalanceSinaController::class, 'rptTrBalanceSina_modal'])->name('rptTrBalanceSinaModal')->middleware('auth');
Route::get('rptTrBalanceSinaXls/{m_date}/{y_date}/{acc_no}/{acc_no_end}/{code_div}', [RptTrBalanceSinaController::class, 'rptTrBalanceSina_xls'])->name('rptTrBalanceSinaXls')->middleware('auth');


Route::get('password', [UserController::class, 'password'])->name('password');
Route::post('password', [UserController::class, 'password_action'])->name('password.action');
Route::get('logout', [UserController::class, 'logout'])->name('logout');
