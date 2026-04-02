import React, { useState } from 'react';
import { Document, Page, pdfjs } from 'react-pdf';

// Workerの設定（CDN経由）
pdfjs.GlobalWorkerOptions.workerSrc = `//unpkg.com/pdfjs-dist@${pdfjs.version}/build/pdf.worker.min.mjs`;

const GuideView = () => {
    const [numPages, setNumPages] = useState(null);
    const [pageNumber, setPageNumber] = useState(1);

    // 【追加】目次のデータ（Wordの目次に対応させて手動で設定します）
     const tableOfContents = [
        { title: "1. 概要", page: 1 },
        { title: "2. 変更履歴", page: 1 },
        { title: "3. 目次", page: 2 },
        { title: "4. 実装図", page: 4 },
        { title: "5. 要件定義", page: 5 },
        { title: "6. 基本設計", page: 7 },
        { title: "7. 環境整備", page: 15 },
        { title: "8. 完全ロードマップ", page: 17 },
        { title: "9. 実装済み機能", page: 19 },
        { title: "10. プロジェクト全ファイル構成", page: 21 },
        { title: "11. 今後追加可能な機能（拡張アイディア）", page: 24 },
        { title: "12. ファイル詳細", page: 25 },
    ];

    function onDocumentLoadSuccess({ numPages }) {
        setNumPages(numPages);
    }

    // 【追加】特定のページへスキップする関数
    const goToPage = (p) => {
        if (p >= 1 && p <= numPages) {
            setPageNumber(p);
        }
    };

    return (
        <div style={{ display: 'flex', gap: '20px', padding: '20px' }}>
            {/* 左側：目次エリア */}
            <div style={{ width: '250px', borderRight: '1px solid #ccc', paddingRight: '20px' }}>
                <h3>目次</h3>
                <ul style={{ listStyle: 'none', padding: 0 }}>
                    {tableOfContents.map((item, index) => (
                        <li key={index} style={{ marginBottom: '10px' }}>
                            <button 
                                onClick={() => goToPage(item.page)}
                                style={{
                                    background: 'none',
                                    border: 'none',
                                    color: 'blue',
                                    textDecoration: 'underline',
                                    cursor: 'pointer',
                                    textAlign: 'left'
                                }}
                            >
                                {item.title} ({item.page}P)
                            </button>
                        </li>
                    ))}
                </ul>
            </div>

            {/* 右側：PDF表示エリア */}
            <div>
                <div style={{ marginBottom: '10px' }}>
                    <button onClick={() => goToPage(pageNumber - 1)} disabled={pageNumber <= 1}>前へ</button>
                    <span style={{ margin: '0 15px' }}>Page {pageNumber} of {numPages}</span>
                    <button onClick={() => goToPage(pageNumber + 1)} disabled={pageNumber >= numPages}>次へ</button>
                </div>

                <div style={{ border: '1px solid #000' }}>
                    <Document file="/storage/guide.pdf" onLoadSuccess={onDocumentLoadSuccess}>
                        <Page pageNumber={pageNumber} width={700} renderTextLayer={false} renderAnnotationLayer={false} />
                    </Document>
                </div>
            </div>
        </div>
    );
};

export default GuideView;