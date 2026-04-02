<x-app-layout>
    {{-- ★ここが重要：x-slot で囲むことで Dashboard という文字を上書きします --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🤖 オートメーション承認結果 ({{ $month }})
            </h2>
            <a href="{{ route('admin.monthly_report') }}" style="color: #007bff; text-decoration: none; font-weight: bold; font-size: 0.9em;">
                ← 月次集計へ戻る
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <thead>
                        <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; text-align: left;">
                            <th style="padding: 12px;">社員番号</th>
                            <th style="padding: 12px;">氏名</th>
                            <th style="padding: 12px;">所属</th>
                            <th style="padding: 12px;">総稼働</th>
                            <th style="padding: 12px;">総残業</th>
                            <th style="padding: 12px;">ステータス</th>
                            <th style="padding: 12px;">不正入力・不備リスト</th>
                            <th style="padding: 12px;">入力日数</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $res)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 12px;">{{ $res['emp_code'] }}</td>
                            <td style="padding: 12px; font-weight: bold;">{{ $res['name'] }}</td>
                            <td style="padding: 12px;">{{ $res['dept'] }}</td>
                            <td style="padding: 12px;">{{ number_format($res['total_work'], 2) }}h</td>
                            <td style="padding: 12px;">{{ number_format($res['total_ot'], 2) }}h</td>
                            <td style="padding: 12px;">
                                <span style="color: {{ $res['status'] == '承認済み' ? '#28a745' : '#dc3545' }}; font-weight: bold;">
                                    {{ $res['status'] }}
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                @if(empty($res['errors']))
                                    <span style="color: #999;">なし</span>
                                @else
                                    <ul style="color: #dc3545; font-size: 0.85em; margin: 0; padding-left: 20px;">
                                        @foreach($res['errors'] as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td style="padding: 12px;">
                                {{ $res['filled_days'] ?? 0 }} / {{ $res['threshold'] ?? '-' }}日
                                @if(($res['filled_days'] ?? 0) < ($res['threshold'] ?? 0))
                                    <span style="color: red; font-weight: bold;">(不足)</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</x-app-layout>