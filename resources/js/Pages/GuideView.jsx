import React, { useState, useEffect } from 'react';
import { Document, Page, pdfjs } from 'react-pdf';
import { Head } from '@inertiajs/react';

// Workerの設定
pdfjs.GlobalWorkerOptions.workerSrc = `//unpkg.com/pdfjs-dist@${pdfjs.version}/build/pdf.worker.min.mjs`;

const GuideView = () => {
    const [numPages, setNumPages] = useState(null);
    const [pageNumber, setPageNumber] = useState(1);
    const [pdfWidth, setPdfWidth] = useState(800);

  const tableOfContents = [
        { title: "トップページ", page: 1 },
        { title: "A. 概要", page: 2 },
        { title: "B. フロー図", page: 5 },
        { title: "C. 画面", page: 6 },
      ];

    const updateDimensions = () => {
        const sidebarWidth = 320;
        const calculatedWidth = window.innerWidth - sidebarWidth;
        setPdfWidth(calculatedWidth > 400 ? calculatedWidth : 400);
    };

    useEffect(() => {
        updateDimensions();
        window.addEventListener('resize', updateDimensions);
        return () => window.removeEventListener('resize', updateDimensions);
    }, []);

    const onDocumentLoadSuccess = ({ numPages }) => {
        setNumPages(numPages);
    };

    const goToPage = (p) => {
        if (p >= 1 && p <= numPages) {
            setPageNumber(p);
        }
    };

    // ★ 修正点：InertiaのLinkを使わず、ブラウザの遷移機能で強制リロード
    const handleBackToMenu = (e) => {
        e.preventDefault();
        window.location.href = '/admin/menu';
    };

    return (
        <div style={{ 
            display: 'flex', 
            height: '100vh', 
            width: '100vw',
            overflow: 'hidden', 
            backgroundColor: '#525659',
            position: 'fixed', 
            top: 0, 
            left: 0,
            zIndex: 9999 // 最前面を確保
        }}>
            <Head title="構築ガイド" /> 

            {/* 左側：サイドバー */}
            <div style={{ 
                width: '280px', minWidth: '280px', backgroundColor: '#ffffff',
                borderRight: '1px solid #333', padding: '20px', overflowY: 'auto',
                boxShadow: '2px 0 5px rgba(0,0,0,0.2)', zIndex: 10, display: 'flex', flexDirection: 'column'
            }}>
                <div style={{ marginBottom: '25px' }}>
                    <button 
                        onClick={handleBackToMenu}
                        style={{
                            display: 'block', padding: '10px 15px', backgroundColor: '#6c757d',
                            color: '#fff', border: 'none', borderRadius: '4px', fontSize: '0.9rem',
                            fontWeight: 'bold', textAlign: 'center', width: '100%', cursor: 'pointer'
                        }}
                    >
                        ← 管理者メニューに戻る
                    </button>
                </div>

                <h3 style={{ borderBottom: '2px solid #333', paddingBottom: '10px', marginBottom: '20px' }}>目次</h3>
                <ul style={{ listStyle: 'none', padding: 0 }}>
                    {tableOfContents.map((item, index) => (
                        <li key={index} style={{ marginBottom: '12px' }}>
                            <button 
                                onClick={() => goToPage(item.page)}
                                style={{
                                    background: 'none', border: 'none',
                                    color: pageNumber === item.page ? '#d9534f' : '#007bff',
                                    textDecoration: 'underline', cursor: 'pointer', textAlign: 'left',
                                    fontSize: '0.9rem', fontWeight: pageNumber === item.page ? 'bold' : 'normal'
                                }}
                            >
                                {item.title} ({item.page}P)
                            </button>
                        </li>
                    ))}
                </ul>
            </div>

            {/* 右側：PDFエリア */}
            <div style={{ flexGrow: 1, display: 'flex', flexDirection: 'column', height: '100vh', overflow: 'hidden' }}>
                <div style={{ 
                    display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '20px', 
                    padding: '10px', backgroundColor: '#323639', color: '#fff'
                }}>
                    <button onClick={() => goToPage(pageNumber - 1)} disabled={pageNumber <= 1} style={{ padding: '5px 15px' }}>前へ</button>
                    <span style={{ minWidth: '120px', textAlign: 'center' }}>{pageNumber} / {numPages}</span>
                    <button onClick={() => goToPage(pageNumber + 1)} disabled={pageNumber >= numPages} style={{ padding: '5px 15px' }}>次へ</button>
                </div>

                <div style={{ flexGrow: 1, overflowY: 'auto', display: 'flex', justifyContent: 'center', alignItems: 'flex-start', padding: '20px' }}>
                    <div style={{ boxShadow: '0 10px 25px rgba(0,0,0,0.5)', backgroundColor: '#fff', lineHeight: 0 }}>
                        <Document file="/pdf/kintai-app_figure.pdf" onLoadSuccess={onDocumentLoadSuccess}>
                            <Page pageNumber={pageNumber} width={pdfWidth} renderTextLayer={false} renderAnnotationLayer={false} />
                        </Document>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default GuideView;