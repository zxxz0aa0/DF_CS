<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Models\ReportConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportConfigurationController extends Controller
{
    /**
     * 儲存報表組合
     */
    public function store(Request $request)
    {
        // 驗證權限
        if (!auth()->user()->can('create report configurations')) {
            abort(403, '無權限建立報表組合');
        }

        // 驗證資料
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'report_type' => 'required|string|max:50',
            'filters' => 'required|array',
        ], [
            'name.required' => '報表組合名稱為必填',
            'report_type.required' => '報表類型為必填',
            'filters.required' => '篩選條件為必填',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 建立報表組合
        $config = ReportConfiguration::create([
            'name' => $request->input('name'),
            'report_type' => $request->input('report_type'),
            'filters' => $request->input('filters'),
        ]);

        return back()->with('success', '報表組合已儲存');
    }

    /**
     * 更新報表組合
     */
    public function update(Request $request, ReportConfiguration $reportConfiguration)
    {
        // 驗證權限
        if (!auth()->user()->can('edit report configurations')) {
            abort(403, '無權限編輯報表組合');
        }

        // 驗證資料
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'filters' => 'required|array',
        ], [
            'name.required' => '報表組合名稱為必填',
            'filters.required' => '篩選條件為必填',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 更新報表組合
        $reportConfiguration->update([
            'name' => $request->input('name'),
            'filters' => $request->input('filters'),
        ]);

        return back()->with('success', '報表組合已更新');
    }

    /**
     * 刪除報表組合
     */
    public function destroy(ReportConfiguration $reportConfiguration)
    {
        // 驗證權限
        if (!auth()->user()->can('delete report configurations')) {
            abort(403, '無權限刪除報表組合');
        }

        $reportConfiguration->delete();

        return back()->with('success', '報表組合已刪除');
    }
}
