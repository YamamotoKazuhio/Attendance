/**
 * Attendance.js - 勤務表管理・申請ワークフロー 最終統合版
 */

// --- 1. グローバル定数・変数の宣言 ---
let currentYear, currentMonth, daysInMonth;

const shiftTimes = {
    "常勤：09:00 - 18:00": ["09:00", "18:00", 1.0],
    "早番：06:00 - 15:00": ["06:00", "15:00", 1.0],
    "遅番：14:00 - 23:00": ["14:00", "23:00", 1.0],
    "深夜：22:00 - 07:00": ["22:00", "08:00", 2.0],
};
const attendanceTime = { 有給: 8.0, 特別休暇: 8.0, 欠勤: -8.0 };

// --- 2. 初期化処理 ---
document.addEventListener("DOMContentLoaded", () => {
    console.log("Attendance.js: Initializing...");

    try {
        const dateStr =
            window.currentMonthStr || new Date().toISOString().slice(0, 7);
        [currentYear, currentMonth] = dateStr.split("-").map(Number);
        daysInMonth = new Date(currentYear, currentMonth, 0).getDate();

        const titleEl = document.getElementById("title");
        if (titleEl) {
            titleEl.textContent = `${currentYear}年${currentMonth.toString().padStart(2, "0")}月分 勤務表`;
        }
    } catch (e) {
        console.error("Date Initialization Error:", e);
    }

    initMonthNav();
    initLeaveModal();
    loadData();
    registerLeaveValidation();
});

// --- 3. カレンダー描画ロジック ---
function loadData() {
    const tableBody = document.getElementById("tableBody");
    if (!tableBody) return;

    tableBody.innerHTML = "";
    const db = window.savedDataFromServer || [];
    const map = {};

    db.forEach((i) => {
        if (!map[i.work_date]) map[i.work_date] = [];
        map[i.work_date].push(i);
    });

    for (let d = 1; d <= daysInMonth; d++) {
        const dk = `${currentYear}-${String(currentMonth).padStart(2, "0")}-${String(d).padStart(2, "0")}`;
        const entries = map[dk] || [null];
        entries.forEach((e) => createRow(d, e));
    }
    calculateTotals();
}

function createRow(day, dbEntry = null) {
    const tableBody = document.getElementById("tableBody");
    const row = document.createElement("tr");
    const dateKey = `${currentYear}-${String(currentMonth).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
    const date = new Date(currentYear, currentMonth - 1, day);
    const dayLabels = ["日", "月", "火", "水", "木", "金", "土"];
    const dayStr = `${currentMonth}/${day}(${dayLabels[date.getDay()]})`;

    row.setAttribute("data-date", dateKey);

    if (
        date.getDay() === 0 ||
        (window.japaneseHolidays && window.japaneseHolidays.includes(dateKey))
    ) {
        row.classList.add("sunday");
    } else if (date.getDay() === 6) {
        row.classList.add("saturday");
    }

    const s = dbEntry ? parseInt(dbEntry.is_locked) : 0;
    const att = dbEntry ? dbEntry.attendance_type : "";
    const wp = dbEntry ? dbEntry.workplace : "社内・本社";
    const sh = dbEntry ? dbEntry.shift : "";
    const st = dbEntry
        ? dbEntry.start_time
            ? dbEntry.start_time.substring(0, 5)
            : ""
        : "";
    const ed = dbEntry
        ? dbEntry.end_time
            ? dbEntry.end_time.substring(0, 5)
            : ""
        : "";
    const rs = dbEntry ? (parseFloat(dbEntry.rest_time) || 0).toFixed(2) : "";
    const ot = dbEntry
        ? (parseFloat(dbEntry.overtime) || 0).toFixed(2)
        : "0.00";
    const wk = dbEntry
        ? (parseFloat(dbEntry.work_time) || 0).toFixed(2)
        : "0.00";

    const btnT =
        s === 3
            ? "確定済み"
            : s === 2
              ? "承認済み"
              : s === 1
                ? "承認待ち"
                : "登録";

    // 詳細ボタンを追加し、onclickを設定
    row.innerHTML = `
        <td><button type="button" onclick="handleStatusChange(this)" class="status-btn">${btnT}</button></td>
        <td>${dayStr}</td>
        <td>
            <button type="button" onclick="insertSpecialRow(this)" class="special-btn">特殊</button>
        </td>
        <td><select onchange="updateTimes(this)">
            <option value=""></option>
            ${["出勤", "欠勤", "遅刻", "早退", "午前半休", "午後半休", "有給", "特別休暇", "休日", "休出", "フレックス"].map((v) => `<option ${att === v ? "selected" : ""}>${v}</option>`).join("")}
        </select></td>
        <td><select onchange="saveData()">
            ${["社内・本社", "社内・支店（営業所）", "社内・テレワーク", "社外・（外勤）"].map((v) => `<option ${wp === v ? "selected" : ""}>${v}</option>`).join("")}
        </select></td>
        <td><select onchange="updateTimes(this)">
            <option value=""></option>${Object.keys(shiftTimes)
                .map(
                    (v) =>
                        `<option ${sh === v ? "selected" : ""}>${v}</option>`,
                )
                .join("")}
        </select></td>
        <td><input type="time" onchange="calcRowTime(this.closest('tr'))" value="${st}"></td>
        <td><input type="time" onchange="calcRowTime(this.closest('tr'))" value="${ed}"></td>
        <td><input type="text" readonly value="${rs}"></td>
        <td><input type="text" readonly value="${ot}"></td>
        <td><input type="text" readonly value="${wk}"></td>
    `;
    tableBody.appendChild(row);
    applyLockState(row, s);
}

// --- 4. 計算・保存・ステータス制御 ---
function handleStatusChange(btn) {
    if (btn.textContent === "確定済み") return;
    if (!window.isAdmin && btn.textContent === "承認済み") return;

    if (!window.isAdmin) {
        btn.textContent = btn.textContent === "登録" ? "承認待ち" : "登録";
    } else {
        btn.textContent =
            btn.textContent === "承認済み" ? "承認待ち" : "承認済み";
    }
    const s =
        btn.textContent === "承認待ち"
            ? 1
            : btn.textContent === "承認済み"
              ? 2
              : 0;
    applyLockState(btn.closest("tr"), s);
    saveData();
}

function applyLockState(row, s) {
    const shouldLock = s === 3 || (!window.isAdmin && s >= 1);
    row.querySelectorAll("input, select").forEach(
        (el) => (el.disabled = shouldLock),
    );
    // 特殊ボタンもロック
    const specialBtn = row.querySelector(".special-btn");
    if (specialBtn) specialBtn.disabled = shouldLock;
    // 詳細ボタンは常に押せるようにしておく（閲覧のため）
}

/**
 * 詳細ボタン（有給管理状況）の表示
 */
function openDetailModal(btn) {
    // サーバーから渡されている有給データを取得（window変数に格納されている想定）
    const paidLeaveInfo = window.paidLeaveData || {
        total_given: 10,
        total_used: 0,
        balance: 10,
        history: [{ date: "2025/07/14", days: 10, type: "法定付与" }],
    };

    const modal = document.getElementById("detailModal");
    if (!modal) {
        // モーダルがない場合はアラートで整形して表示
        let historyStr = paidLeaveInfo.history
            .map((h) => `${h.date} | ${h.days}日 | ${h.type}`)
            .join("\n");

        alert(
            `【有給休暇 管理状況】\n` +
                `累計付与: ${paidLeaveInfo.total_given} 日\n` +
                `累計消化: ${paidLeaveInfo.total_used} 日\n` +
                `現在残高: ${paidLeaveInfo.balance} 日\n\n` +
                `[付与履歴]\n付与日 | 付与日数 | 種別\n${historyStr}`,
        );
        return;
    }

    // HTML側のモーダル要素がある場合は、そこに値を流し込む
    document.getElementById("total_given").textContent =
        paidLeaveInfo.total_given;
    document.getElementById("total_used").textContent =
        paidLeaveInfo.total_used;
    document.getElementById("balance").textContent = paidLeaveInfo.balance;

    // 履歴テーブルの更新
    const historyBody = document.getElementById("historyBody");
    if (historyBody) {
        historyBody.innerHTML = paidLeaveInfo.history
            .map(
                (h) => `
            <tr>
                <td>${h.date}</td>
                <td>${h.days}日</td>
                <td>${h.type}</td>
            </tr>
        `,
            )
            .join("");
    }

    modal.style.display = "block";
}

function updateTimes(el) {
    const row = el.closest("tr");
    const attSelect = row.cells[3].querySelector("select");
    const att = attSelect.value;
    const shSelect = row.cells[5].querySelector("select");
    const sh = shSelect.value;

    const startI = row.cells[6].querySelector("input");
    const endI = row.cells[7].querySelector("input");
    const restI = row.cells[8].querySelector("input");
    const otCell = row.cells[9].querySelector("input");
    const wkCell = row.cells[10].querySelector("input");

    // 1. まず数値をリセット（ゴミデータを消す）
    wkCell.value = "0.00";
    otCell.value = "0.00";

    // 2. 欠勤・有給の特殊処理
    if (att === "欠勤" || att === "有給") {
        if (shSelect) shSelect.selectedIndex = 0;
        startI.value = "";
        endI.value = "";
        restI.value = "0.00";

        if (att === "欠勤") {
            wkCell.value = "-8.00";
        } else {
            wkCell.value = "8.00";
        }

        // 【重要】ここで計算と保存を強制実行する
        calculateTotals();
        saveData();
        return; // ここで終了してOK
    }

    // 3. 休日・出勤の処理
    if (att === "休日") {
        startI.value = "";
        endI.value = "";
        restI.value = "0.00";
    } else if (shiftTimes[sh]) {
        [startI.value, endI.value, restI.value] = [
            shiftTimes[sh][0],
            shiftTimes[sh][1],
            shiftTimes[sh][2].toFixed(2),
        ];
    }

    // 4. 通常の計算と保存を実行
    calcRowTime(row);
}

function calcRowTime(row) {
    const att = row.cells[3].querySelector("select").value;
    const start = row.cells[6].querySelector("input").value;
    const end = row.cells[7].querySelector("input").value;
    const rest = parseFloat(row.cells[8].querySelector("input").value) || 0;
    const otCell = row.cells[9].querySelector("input");
    const wkCell = row.cells[10].querySelector("input");

    if (attendanceTime[att]) {
        otCell.value = "0.00";
        wkCell.value = attendanceTime[att].toFixed(2);
    } else if (start && end) {
        let [sh, sm] = start.split(":").map(Number);
        let [eh, em] = end.split(":").map(Number);
        let sMin = sh * 60 + sm;
        let eMin = eh * 60 + em;
        if (eMin < sMin) eMin += 1440;
        let wkH = (eMin - sMin - rest * 60) / 60;
        wkCell.value = wkH.toFixed(2);
        otCell.value = Math.max(0, wkH - 8).toFixed(2);
    }
    calculateTotals();
    saveData();
}

function calculateTotals() {
    let to = 0,
        tw = 0,
        cd = 0;
    const commuteUnit = document.getElementById("commuteCost");
    const cp = commuteUnit ? parseFloat(commuteUnit.value) || 0 : 0;
    const tableBody = document.getElementById("tableBody");
    if (!tableBody) return;

    [...tableBody.rows].forEach((r) => {
        const otInput = r.cells[9]?.querySelector("input");
        to += otInput ? parseFloat(otInput.value) || 0 : 0;
        const wkInput = r.cells[10]?.querySelector("input");
        tw += wkInput ? parseFloat(wkInput.value) || 0 : 0;
        const attSelect = r.cells[3]?.querySelector("select");
        const att = attSelect ? attSelect.value : "";
        if (
            [
                "出勤",
                "遅刻",
                "早退",
                "午前半休",
                "午後半休",
                "フレックス",
                "休出",
            ].includes(att)
        )
            cd++;
    });

    if (document.getElementById("totalOvertime"))
        document.getElementById("totalOvertime").textContent = to.toFixed(2);
    if (document.getElementById("totalWorkTime"))
        document.getElementById("totalWorkTime").textContent = tw.toFixed(2);
    if (document.getElementById("totalCommuteCost"))
        document.getElementById("totalCommuteCost").textContent =
            `${(cd * cp).toLocaleString()}円`;
}

function saveData() {
    const tableBody = document.getElementById("tableBody");
    if (!tableBody) return;

    const data = [];
    [...tableBody.rows].forEach((row) => {
        const workDate = row.getAttribute("data-date");
        if (!workDate) return;

        // セルが存在し、その中に要素があるか確認しながら取得
        const typeVal = row.cells[3]?.querySelector("select")?.value || "";
        const workplaceVal = row.cells[4]?.querySelector("select")?.value || ""; // 勤務先
        const shiftVal = row.cells[5]?.querySelector("select")?.value || ""; // シフト
        const startVal = row.cells[6]?.querySelector("input")?.value || "";
        const endVal = row.cells[7]?.querySelector("input")?.value || "";
        const restVal = row.cells[8]?.querySelector("input")?.value || 0;
        const otVal = row.cells[9]?.querySelector("input")?.value || 0;
        const workVal = row.cells[10]?.querySelector("input")?.value || 0;

        const statusBtn = row.cells[0]?.querySelector("button");
        const btnText = statusBtn ? statusBtn.textContent : "";
        const statusValue =
            btnText === "確定済み"
                ? 3
                : btnText === "承認済み"
                  ? 2
                  : btnText === "承認待ち"
                    ? 1
                    : 0;

        data.push({
            date: workDate,
            type: row.cells[3]?.querySelector("select")?.value || "",
            workplace: workplaceVal, // 修正：空文字から取得した値へ
            shift: shiftVal, // 修正：空文字から取得した値へ
            start: row.cells[6]?.querySelector("input")?.value || "",
            end: row.cells[7]?.querySelector("input")?.value || "",
            rest: row.cells[8]?.querySelector("input")?.value || 0,
            ot: row.cells[9]?.querySelector("input")?.value || 0,
            work: row.cells[10]?.querySelector("input")?.value || 0,
            is_locked: statusValue,
        });
    });

    fetch("/attendance/save", {
        // ここを必ず一致させる
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
        },
        body: JSON.stringify({
            attendances: data,
            user_id: window.displayUserId,
        }),
    })
        .then((res) => {
            // レスポンスがJSONかどうかをチェック
            const contentType = res.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                return res.json().then((data) => {
                    if (!res.ok)
                        throw new Error(data.message || "サーバーエラー");
                    alert("保存しました。");
                    refreshMonthlyStatus(); // ステータス表示をリアルタイム更新
                    console.log("Success:", data);
                });
            } else {
                // JSONでない（HTMLエラー画面など）が返ってきた場合
                throw new Error(
                    "サーバーで予期せぬエラーが発生しました。ログを確認してください。",
                );
            }
        })
        .catch((err) => {
            console.error("Save Error:", err);
            alert("保存に失敗しました。\n" + err.message);
        });
}

function registerLeaveValidation() {
    const leaveForm = document.getElementById("leaveForm");
    if (!leaveForm) return;
    leaveForm.addEventListener("submit", function (e) {
        const typeSelect = document.getElementById("leave_type");
        const remaining = parseFloat(window.dbRemainingPaid) || 0;
        const selectedOption = typeSelect.options[typeSelect.selectedIndex];
        const requested =
            parseFloat(selectedOption.getAttribute("data-days")) || 0;
        if (requested > 0 && requested > remaining) {
            e.preventDefault();
            alert(
                `有給残数が不足しています（残り: ${remaining}日）。\n不足分は「欠勤」等で申請してください。`,
            );
            return false;
        }
    });
}

function initMonthNav() {
    const p = document.getElementById("btnPrevMonth");
    const n = document.getElementById("btnNextMonth");
    const c = document.getElementById("btnCurrentMonth");
    if (p) p.onclick = () => moveMonth(-1);
    if (n) n.onclick = () => moveMonth(1);
    if (c)
        c.onclick = () => {
            const today = new Date();
            updateUrl(
                `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, "0")}`,
            );
        };
}

function moveMonth(offset) {
    const date = new Date(currentYear, currentMonth - 1 + offset, 1);
    updateUrl(
        `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`,
    );
}

function updateUrl(monthParam) {
    const url = new URL(window.location.href);
    url.searchParams.set("month", monthParam);
    window.location.href = url.toString();
}

function initLeaveModal() {
    const modal = document.getElementById("leaveModal");
    const openBtn = document.getElementById("btnOpenLeaveModal");
    const closeBtn = document.getElementById("btnCloseLeaveModal");
    if (!modal || !openBtn) return;
    openBtn.onclick = () => (modal.style.display = "block");
    if (closeBtn) closeBtn.onclick = () => (modal.style.display = "none");
    window.onclick = (e) => {
        if (e.target == modal) modal.style.display = "none";
    };
}

function insertSpecialRow(btn) {
    const mainRow = btn.closest("tr");
    const dk = mainRow.getAttribute("data-date");
    const dayLabel = mainRow.cells[1].innerText;
    const nr = document.createElement("tr");
    nr.classList.add("special-row");
    nr.setAttribute("data-date", dk);
    nr.innerHTML = `
        <td><button type="button" class="status-btn">登録</button></td>
        <td>${dayLabel}</td>
        <td><button type="button" onclick="this.closest('tr').remove(); calculateTotals(); saveData();">削除</button></td>
        <td>特殊</td><td>―</td><td>―</td>
        <td><input type="time" onchange="calcRowTime(this.closest('tr'))"></td>
        <td><input type="time" onchange="calcRowTime(this.closest('tr'))"></td>
        <td><input type="text" value="0.00" readonly></td>
        <td><input type="text" value="0.00" readonly></td>
        <td><input type="text" value="0.00" readonly></td>
    `;
    mainRow.after(nr);
}

/**
 * 画面上のボタンの状態をスキャンして、上部のステータス表示を更新する
 */
function refreshMonthlyStatus() {
    const rows = document.querySelectorAll('#tableBody tr');
    if (rows.length === 0) return;

    let totalDays = rows.length;
    let regBtnCount = 0;      // [登録]ボタンが残っている数
    let pendingCount = 0;     // データがあるが未承認（承認待ち）の数
    let approvedCount = 0;    // [承認済み]（編集不可）の数

    rows.forEach(row => {
        const firstCell = row.cells[0];
        const btn = firstCell.querySelector('button');
        const cellText = firstCell.innerText.trim();

        if (btn && btn.innerText.includes('登録')) {
            regBtnCount++;
        } else if (cellText.includes('承認済み')) {
            approvedCount++;
        } else {
            // 「更新」ボタンになっている、またはデータが存在する状態
            pendingCount++;
        }
    });

    // 表示要素を取得
    const statusContainer = document.querySelector('#user_info span[style*="font-weight: bold"] + span');
    if (!statusContainer) return;

    let text = "";
    let color = "";
    let bgColor = "";
    let borderColor = "";

    if (regBtnCount === totalDays) {
        text = "未登録";
        color = "#666";
        bgColor = "#eee";
        borderColor = "#ccc";
    } else if (approvedCount === totalDays) {
        text = "✅ 承認済み（修正不可）";
        color = "#2e7d32";
        bgColor = "#e6ffed";
        borderColor = "#2e7d32";
    } else if (approvedCount > 0) {
        text = "⚠️ 承認済み（一部のみ）";
        color = "#f57c00";
        bgColor = "#fff3e0";
        borderColor = "#f57c00";
    } else {
        text = "🚩 確定（承認待ち）";
        color = "#d32f2f";
        bgColor = "#fff5f5";
        borderColor = "#d32f2f";
    }

    // 見た目を更新
    statusContainer.innerText = text;
    statusContainer.style.color = color;
    statusContainer.style.background = bgColor;
    statusContainer.style.borderColor = borderColor;
}

// データ保存（登録ボタン押下時）の成功レスポンス内でこの関数を呼び出す
// 例: .then(data => { 
//         alert("保存しました");
//         refreshMonthlyStatus(); 
//     })

// --- 基本情報（所属・通勤費）の更新処理 ---
function updateBasicInfo(el) {
    const userId = window.displayUserId;
    const department = document.getElementById("department")?.value;
    const commuteFee = document.getElementById("commuteCost")?.value;

    fetch("/attendance/update-basic-info", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
        },
        body: JSON.stringify({
            user_id: userId,
            department: department,
            commute_fee: commuteFee,
        }),
    })
        .then((res) => res.json())
        .then((data) => console.log(data.message))
        .catch((err) => console.error("UpdateBasicInfo Error:", err));
}
