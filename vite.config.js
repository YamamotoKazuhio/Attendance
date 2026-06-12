import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/js/Attendance.js", "resources/css/app.css"],
            refresh: true,
        }),
        react(),
    ],
    server: {
        // IPv6 ([::1]) による接続トラブルを避けるための設定
        host: "localhost",
    },
});
