/**
 * 共通ユーティリティモジュール
 */

// CSRFトークンの取得（API通信のたびに必要）
export function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : "";
}

// ターゲット年月と日数を取得
export function getTargetDate() {
    const dateStr =
        window.currentMonthStr || new Date().toISOString().slice(0, 7);
    const [year, month] = dateStr.split("-").map(Number);
    const daysInMonth = new Date(year, month, 0).getDate();
    return { year, month, daysInMonth };
}

// 日付フォーマット用の曜日の日本語リスト
export const dayLabels = ["日", "月", "火", "水", "木", "金", "土"];
