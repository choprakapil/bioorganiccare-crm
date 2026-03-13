import React from 'react';

export default function DentalChartSVG({ 
  selectedTeeth = [], 
  toothStatus = {}, 
  toothHistory = {},
  onTeethChange 
}) {
  const [hoveredTooth, setHoveredTooth] = React.useState(null);
  const [tooltipPos, setTooltipPos] = React.useState({ x: 0, y: 0 });

  const STATUS_COLORS = {
    pending: "#10B981", // Emerald for current visit
    decay: "#EF4444",
    filled: "#3B82F6",
    crown: "#EAB308",
    missing: "#9CA3AF"
  };

  const getToothFill = (id) => {
    if (selectedTeeth.includes(id)) return "#2563EB";
    const status = toothStatus[id];
    // Color Priority: Selected > Pending (Current) > Status (Historical)
    if (status) return STATUS_COLORS[status] || "#FFFFFF";
    return "#FFFFFF";
  };

  const handleToothClick = (toothId) => {
    const updated = selectedTeeth.includes(toothId)
      ? selectedTeeth.filter(t => t !== toothId)
      : [...selectedTeeth, toothId];
    
    if (onTeethChange) {
      onTeethChange(updated);
    }
  };

  const isSelected = (toothId) => selectedTeeth.includes(toothId);

  return (
    <div className="tooth-chart-container p-4 bg-[#fdf6ec] rounded-[2.5rem] border border-orange-100 shadow-sm relative overflow-hidden">
      <style>{`
        .tooth {
          cursor: pointer;
          transition: all 0.2s ease;
          stroke: #e2e8f0;
          stroke-width: 0.5px;
        }
        .tooth:hover {
          fill: #f8fafc !important;
          stroke: #cbd5e1;
        }
        .tooth.selected {
          fill: #2563EB !important;
          stroke: #1e40af;
          filter: drop-shadow(0 0 3px rgba(37, 99, 235, 0.4));
        }
        .history-indicator {
          fill: #f59e0b;
          stroke: white;
          stroke-width: 1px;
          filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1));
          pointer-events: none;
        }
        .tooth-label.has-history {
          fill: #94a3b8;
        }
      `}</style>
      
      <div className="flex justify-between items-center mb-4 px-2">
        <h3 className="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Clinical Dental Map</h3>
        <div className="flex gap-3 items-center">
            {selectedTeeth.length > 0 && (
                <button 
                    onClick={(e) => { e.preventDefault(); onTeethChange([]); }}
                    className="text-[10px] font-bold text-slate-400 hover:text-red-500 transition-colors uppercase tracking-widest"
                >
                    Reset
                </button>
            )}
            <span className="text-[10px] font-black px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full border border-blue-100">
                {selectedTeeth.length} Selected
            </span>
        </div>
      </div>

      {/* Status Legend */}
      <div className="flex flex-wrap gap-4 px-2 mb-6 animate-in fade-in duration-500">
         {Object.entries(STATUS_COLORS).map(([status, color]) => (
           <div key={status} className="flex items-center gap-2">
              <div className="w-3 h-3 rounded-full shadow-sm border border-slate-200" style={{ backgroundColor: color }} />
              <span className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{status}</span>
           </div>
         ))}
         <div className="flex items-center gap-2">
            <div className="w-2.5 h-2.5 rounded-full bg-amber-500 border border-white" />
            <span className="text-[10px] font-black text-amber-600 uppercase tracking-widest">Has History</span>
         </div>
      </div>

      <div className="relative">
        <svg
            version="1.1"
            xmlns="http://www.w3.org/2000/svg"
            xmlnsXlink="http://www.w3.org/1999/xlink"
            x="0px"
            y="0px"
            viewBox="0 0 450 700"
            enableBackground="new 0 0 450 700"
            xmlSpace="preserve"
            className="w-full h-auto max-h-[500px] mx-auto"
        >
            <g id="toothLabels">
            {[32,31,30,29,28,27,26,25,24,23,22,21,20,19,18,17,16,15,14,13,12,11,10,9,8,7,6,5,4,3,2,1].map(num => {
                const hasHistory = toothHistory[num]?.length > 0;
                return (
                    <g key={`grp${num}`} transform={getTransform(num)}>
                        <text 
                            id={`lbl${num}`} 
                            className={`tooth-label ${isSelected(num) ? 'selected' : ''} ${hasHistory ? 'has-history' : ''}`}
                            fontFamily="'Avenir-Heavy'" 
                            fontSize={num === 27 || num === 23 ? "17px" : num === 26 || num === 25 || num === 24 || num === 22 ? "18px" : "21px"}
                        >
                            {num}
                        </text>
                        {hasHistory && (
                            <circle cx="12" cy="-8" r="4" className="history-indicator" />
                        )}
                    </g>
                );
            })}
            </g>
            <g id="Spots"
                onMouseEnter={(e) => {
                    const tooth = e.target.closest('.tooth');
                    if (tooth) {
                        setHoveredTooth(tooth.getAttribute('data-key'));
                        setTooltipPos({ x: e.clientX, y: e.clientY });
                    }
                }}
                onMouseMove={(e) => {
                    const tooth = e.target.closest('.tooth');
                    if (tooth) {
                        setTooltipPos({ x: e.clientX, y: e.clientY });
                    }
                }}
                onMouseLeave={() => setHoveredTooth(null)}
            >
            <polygon id="Tooth32" className={`tooth ${isSelected(32) ? 'selected' : ''}`} fill={getToothFill(32)} data-key="32" onClick={() => handleToothClick(32)} points="66.7,369.7 59,370.3 51,373.7 43.7,384.3 42.3,392 38.7,406 41,415.3 44.3,420.3 47.3,424 51.7,424.3 57.7,424 62.3,422.7 66.7,422.7 71,424.3 76.3,422.7 80.7,419.3 84.7,412.3 85.3,405 87.3,391.7 85,380 80.7,375 73.7,371.3" />
            <polygon id="Tooth31" className={`tooth ${isSelected(31) ? 'selected' : ''}`} fill={getToothFill(31)} data-key="31" onClick={() => handleToothClick(31)} points="76,425.7 80.3,427.7 83.3,433 85.3,447.7 84.3,458.7 79.7,472.3 73,475 50.3,479.7 46.7,476.7 37.7,446.3 39.7,438.3 43.3,432 49,426.7 56,424.7 65,424.7" />
            <polygon id="Tooth30" className={`tooth ${isSelected(30) ? 'selected' : ''}`} fill={getToothFill(30)} data-key="30" onClick={() => handleToothClick(30)} points="78.7,476 85,481 90.3,488.3 96.3,499.3 97.7,511.3 93,522 86,526.3 67,533 60.3,529.7 56.3,523.7 51.7,511 47.7,494.7 47.7,488.3 50.3,483.3 55,479.7 67,476.7" />
            <polygon id="Tooth29" className={`tooth ${isSelected(29) ? 'selected' : ''}`} fill={getToothFill(29)} data-key="29" onClick={() => handleToothClick(29)} points="93.3,525 99.3,527.3 108.3,536 114,546.7 115.7,559.3 114.3,567.3 106.3,573 98.3,578.3 88,579 82,575 75,565 69.3,552.3 67.3,542 69.7,536 74.3,531.7 84.3,528.3" />
            <path id="Tooth28" className={`tooth ${isSelected(28) ? 'selected' : ''}`} fill={getToothFill(28)} data-key="28" onClick={() => handleToothClick(28)} d="M117.3,569.7l7.7,1.3l6.3,3.7l6.3,7.7l4,8.3L144,602l-1.3,6.7l-6.7,6.7l-7.7,3.3l-7.3-1l-7-3 l-7.3-7l-5-9l-2-10c0,0-0.7-7,0.3-7.3c1-0.3,5.3-6.7,5.3-6.7l9-5H117.3z" />
            <polygon id="Tooth27" className={`tooth ${isSelected(27) ? 'selected' : ''}`} fill={getToothFill(27)} data-key="27" onClick={() => handleToothClick(27)} points="155.7,611 160.3,615.3 165,624.7 161.7,634.3 156,641.3 149,644 140.7,644.3 133.3,641.3 128.7,634.7 128.7,629 132.7,621.3 137.7,615 143.7,611 149.7,610" />
            <polygon id="Tooth26" className={`tooth ${isSelected(26) ? 'selected' : ''}`} fill={getToothFill(26)} data-key="26" onClick={() => handleToothClick(26)} points="178.3,627 186,629 187.7,633.7 188.7,644 189,657 189.3,662.7 186.3,663.7 176.7,663 168,656.3 159.3,649.7 156.7,644 162,639.3" />
            <polygon id="Tooth25" className={`tooth ${isSelected(25) ? 'selected' : ''}`} fill={getToothFill(25)} data-key="25" onClick={() => handleToothClick(25)} points="214,637 218,642.7 223,654.3 225.7,664 225.3,666.3 219,668.3 206.7,668 196,665.7 190.3,662.7 193,657.3 199.7,647.3 207,638 210.7,635.5" />
            <path id="Tooth24" className={`tooth ${isSelected(24) ? 'selected' : ''}`} fill={getToothFill(24)} data-key="24" onClick={() => handleToothClick(24)} d="M235.3,637c0,0,3-2,4-2.3c1-0.3,4.3,0,4.3,0l5,4.3l5.3,7.3l3.3,6.7l2,7.3l-2,3 l-7.7,2.7 l-10,0.3h-10l-2-6.7l2.7-7.3L235.3,637z" />
            <polygon id="Tooth23" className={`tooth ${isSelected(23) ? 'selected' : ''}`} fill={getToothFill(23)} data-key="23" onClick={() => handleToothClick(23)} points="269.3,624 273.3,624.7 275.3,627.3 279,628.7 281.7,631.3 285.3,634.7 289.3,638.3 292,643.3 291.3,650 287,655 280.7,658.7 272,660 265,660.7 261.3,657.3 261.7,650 263.7,637 264.3,627" />
            <polygon id="Tooth22" className={`tooth ${isSelected(22) ? 'selected' : ''}`} fill={getToothFill(22)} data-key="22" onClick={() => handleToothClick(22)} points="286,629.3 286.7,633.3 291.3,638.7 295.3,642.3 302,644 311.7,643.3 318.3,637.7 321,630 321.3,620.3 317,614.3 308,608 298.3,607 291,609.3 287,612.3 286.7,617.7 287.3,624.7" />
            <polygon id="Tooth21" className={`tooth ${isSelected(21) ? 'selected' : ''}`} fill={getToothFill(21)} data-key="21" onClick={() => handleToothClick(21)} points="331,565.7 335,565.7 341.3,568 349.3,574.3 352.3,578.3 352.7,583.7 350.7,593.7 342.7,604 337.7,609 328,612.7 320,613.3 315,611 308.3,604.7 306.7,598 307.3,591.3 309,584.7 312.7,578.3 318.3,571.7" />
            <polygon id="Tooth20" className={`tooth ${isSelected(20) ? 'selected' : ''}`} fill={getToothFill(20)} data-key="20" onClick={() => handleToothClick(20)} points="334,561 338.7,566 346,570 354.7,573 360.7,571.7 368,568.3 383,545 385.3,532.7 381.3,524.3 374,520.7 363.7,516.3 356.3,515.3 351.3,518.3 346.3,524 340.3,534.3 336,546.7" />
            <path id="Tooth19" className={`tooth ${isSelected(19) ? 'selected' : ''}`} fill={getToothFill(19)} data-key="19" onClick={() => handleToothClick(19)} d="M398,470l4.7,5.7l3,7.7l-0.3,11.7l-6,13.3l-6.3,10.3l-8.3,4.3l-7.3-1l-16.3-7c0,0-2.7-6-3-7.3 c-0.3-1.3-0.3-11-0.3-11l3.7-14.3l3.7-7l5.3-6.7l8-2l9.7-0.7L398,470z" />
            <polygon id="Tooth18" className={`tooth ${isSelected(18) ? 'selected' : ''}`} fill={getToothFill(18)} data-key="18" onClick={() => handleToothClick(18)} points="410,435 408.7,447.3 404.3,459 399.3,467.7 393.7,468 388,466 376.3,466.3 369.7,466.3 365.7,460 364.7,444.7 366.3,434.3 369,424 378.3,417.3 386.7,415.7 391.7,415.3 396,418 399.7,418 404,421.7 407.7,427.3" />
            <polygon id="Tooth17" className={`tooth ${isSelected(17) ? 'selected' : ''}`} fill={getToothFill(17)} data-key="17" onClick={() => handleToothClick(17)} points="371.7,417 378.3,417.3 386.7,415.7 391.7,415.3 397.3,417.7 402.7,416.3 407.7,409.7 406.7,395 401,377.7 397.3,373 390.7,367.3 380,365 373,366.7 367.3,369 364,374.3 360,389 363.3,401.3 367.7,412.3" />
            <polygon id="Tooth16" className={`tooth ${isSelected(16) ? 'selected' : ''}`} fill={getToothFill(16)} data-key="16" onClick={() => handleToothClick(16)} points="404.3,293.7 408.7,299.3 408.7,308 405.3,318.7 401,329.7 392.3,339.7 382.7,341 369,339.7 359,335 354.7,327.7 354.3,316 358.3,304 363.7,294 368.7,294.7 378.7,296 389,296" />
            <polygon id="Tooth15" className={`tooth ${isSelected(15) ? 'selected' : ''}`} fill={getToothFill(15)} data-key="15" onClick={() => handleToothClick(15)} points="362.3,247.3 357.3,251 357,259.3 358.7,268 359.7,279.7 361.3,286.7 365,291.7 371,294.3 392,295 404.3,293.7 410,280.7 412,263.3 407.3,246.7 401,240.3 396,239.7 389.3,243" />
            <polygon id="Tooth14" className={`tooth ${isSelected(14) ? 'selected' : ''}`} fill={getToothFill(14)} data-key="14" onClick={() => handleToothClick(14)} points="359.7,243.7 350.7,224 345.7,211.7 348.7,205 358.3,202.7 375.7,197 388.7,193 393,196 399.3,207 401.3,222.7 400,234.3 394.7,240.7 381.7,244.7 371,246" />
            <polygon id="Tooth13" className={`tooth ${isSelected(13) ? 'selected' : ''}`} fill={getToothFill(13)} data-key="13" onClick={() => handleToothClick(13)} points="386,188.7 383.3,192.7 377.7,196 356.3,203.3 345.7,202.3 341.7,199.7 338.7,196.3 335,188.7 332,177 333.7,169.7 338,164.7 346.3,161 353.7,156.7 360.3,150.3 364,151 370.7,156.3 376.3,164.3 380,170.3 383.3,178.3" />
            <polygon id="Tooth12" className={`tooth ${isSelected(12) ? 'selected' : ''}`} fill={getToothFill(12)} data-key="12" onClick={() => handleToothClick(12)} points="358.7,134.3 360.3,145.7 357.3,152.7 352,157.3 346.3,161 336,164 329.7,163.3 321.7,157.7 314.3,149 310.7,139.3 310,133.7 312.3,127 318.3,125.7 326,122 332.7,116 334.7,114.3 337.7,117.3 343.3,119.7 348.7,122.7 354.3,127.7" />
            <polygon id="Tooth11" className={`tooth ${isSelected(11) ? 'selected' : ''}`} fill={getToothFill(11)} data-key="11" onClick={() => handleToothClick(11)} points="336,93.3 337.7,100 336,104.7 332.7,113.7 324.3,121.3 315.3,125.7 306.3,126 297.3,120.3 294,112 295.7,102.7 299,95 303.3,90 309.3,88 316.3,87.3 322.7,87.3 328,88.3" />
            <polygon id="Tooth10" className={`tooth ${isSelected(10) ? 'selected' : ''}`} fill={getToothFill(10)} data-key="10" onClick={() => handleToothClick(10)} points="310.3,83.3 298,90.7 286,95 276.3,98.3 270.3,93.3 269,82.7 269,69.3 270,58.7 274.7,54.7 282,53 287.7,54.7 297.3,60.3 304,64.3 308.7,68.7 312.3,74 313,81" />
            <polygon id="Tooth9" className={`tooth ${isSelected(9) ? 'selected' : ''}`} fill={getToothFill(9)} data-key="9" onClick={() => handleToothClick(9)} points="273.3,52 266.7,61.7 258.3,72.3 253.3,79.7 247.3,85 239,87.7 232.3,82 224.7,67 222,58.3 219,50 220,44.3 224.3,40.3 230,38.7 237.3,38.7 253,39.3 258.7,41.3 264.3,43.7 268.3,45.7" />
            <polygon id="Tooth8" className={`tooth ${isSelected(8) ? 'selected' : ''}`} fill={getToothFill(8)} data-key="8" onClick={() => handleToothClick(8)} points="176.7,46.3 195,41 203.3,39.7 209.3,40.7 215.3,42.7 217,47 217.7,54.3 215,64.7 212.3,75.7 208,83 201.7,85.7 195.7,86.7 189.7,83.3 183.7,74.7 175,62 171.7,54 172.7,49.7" />
            <path id="Tooth7" className={`tooth ${isSelected(7) ? 'selected' : ''}`} fill={getToothFill(7)} data-key="7" onClick={() => handleToothClick(7)} d="M167,55l6.7,6.3L174,68l0.3,8l1,10l-2,8.3l-4.7,4.3l-6.7,1.7l-8-4.3l-7.3-4.7l-9.3-4.7 l-6.3-5.3l-1-4.3l1.3-5c0,0,3.3-6,4.3-6s5.3-6,6.3-6s10.3-4.7,10.3-4.7L167,55z" />
            <polygon id="Tooth6" className={`tooth ${isSelected(6) ? 'selected' : ''}`} fill={getToothFill(6)} data-key="6" onClick={() => handleToothClick(6)} points="126.3,82 134.3,86.3 139.7,92.3 144.7,104.7 145.7,115.3 143.7,120.7 138,124.3 131.3,125 121,125 114.7,119.3 110.3,112.3 108.3,104.7 108.7,94.7 110.7,88.7 116,84" />
            <polygon id="Tooth5" className={`tooth ${isSelected(5) ? 'selected' : ''}`} fill={getToothFill(5)} data-key="5" onClick={() => handleToothClick(5)} points="109,116.7 116,122.3 122.7,125.3 127.7,131.3 128.3,141 122.7,153.7 114,161.7 105.7,162.3 96.7,161 85.7,156 82,150 81,139.3 86.3,128 93,121.3 100.7,117.3" />
            <polygon id="Tooth4" className={`tooth ${isSelected(4) ? 'selected' : ''}`} fill={getToothFill(4)} data-key="4" onClick={() => handleToothClick(4)} points="82,155.3 102.3,163.3 108.7,172 109.3,182 104.7,192 100,199 94,203.7 85.3,201.7 73.7,201 64.3,196.7 60.3,190.7 59,183.3 61.7,175.3 66.3,167.7 71.3,161.3" />
            <path id="Tooth3" className={`tooth ${isSelected(3) ? 'selected' : ''}`} fill={getToothFill(3)} data-key="3" onClick={() => handleToothClick(3)} d="M92.7,207.3l2,5.3l-1.7,8l-1.7,9l-4,8l-5,7.7l-11,4.7l-13.7,0.7l-10-7l-1.7-5L45,220l3-10.7 l5-7.3l4-3.3l4.7-2.7l5.3,3.7l6.7,1.3c0,0,7.3,1.3,9.3,1.3s6.3,0.7,6.3,0.7L92.7,207.3z" />
            <polygon id="Tooth2" className={`tooth ${isSelected(2) ? 'selected' : ''}`} fill={getToothFill(2)} data-key="2" onClick={() => handleToothClick(2)} points="79.7,288.3 71.7,291 55,293 40.3,291.3 36,287 33,273.7 36.3,260 42,248.7 44.7,244.7 50.3,246.7 56,249 65.3,250.7 74,249.7 80.3,249.7 82.3,254 85.3,259.3 87,267.7 87.7,274.7 85.3,282.7" />
            <polygon id="Tooth1" className={`tooth ${isSelected(1) ? 'selected' : ''}`} fill={getToothFill(1)} data-key="1" onClick={() => handleToothClick(1)} points="33,314.3 38,325.7 45.7,335.7 55.7,341.7 64.7,343 73.3,340 77.7,335.7 81.3,326.3 82,314.3 81.3,302 80.7,292.7 73.7,292 51.3,293.7 38.7,293.7 34,298 31.7,302.3 32,311" />
            </g>
        </svg>

        {/* Clinical History Tooltip */}
        {hoveredTooth && toothHistory[hoveredTooth] && (
            <div 
                className="fixed z-50 pointer-events-none animate-in zoom-in-95 duration-200"
                style={{ left: tooltipPos.x + 15, top: tooltipPos.y - 10 }}
            >
                <div className="bg-slate-900 text-white rounded-2xl p-4 shadow-2xl border border-slate-700 min-w-[180px]">
                    <p className="text-[10px] font-black text-primary uppercase tracking-[0.2em] mb-2">Tooth #{hoveredTooth} History</p>
                    <div className="space-y-3">
                        {toothHistory[hoveredTooth].map((h, i) => (
                            <div key={i} className="border-l-2 border-primary/30 pl-3">
                                <p className="text-xs font-black">{h.procedure}</p>
                                <div className="flex justify-between items-center mt-1">
                                    <span className="text-[9px] font-bold text-slate-400">{new Date(h.date).toLocaleDateString()}</span>
                                    <span className="text-[10px] font-black text-green-400">₹{h.fee.toLocaleString()}</span>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        )}
      </div>

      {selectedTeeth.length > 0 && (
        <div className="mt-4 p-4 bg-slate-50 rounded-2xl border border-slate-100 animate-in fade-in slide-in-from-bottom-2 duration-300">
            <h4 className="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Targeted Teeth</h4>
            <div className="flex flex-wrap gap-1.5">
                {selectedTeeth.sort((a,b) => a-b).map(t => (
                    <span key={t} className="px-2 py-1 bg-white border border-slate-200 text-slate-700 text-[10px] font-black rounded-lg shadow-sm">
                        {t}
                    </span>
                ))}
            </div>
        </div>
      )}
    </div>
  );
}

function getTransform(num) {
    const transforms = {
        32: "matrix(1 0 0 1 97.9767 402.1409)",
        31: "matrix(1 0 0 1 94.7426 449.1693)",
        30: "matrix(1 0 0 1 106.0002 495.5433)",
        29: "matrix(1 0 0 1 118.0002 538.667)",
        28: "matrix(0.9999 -1.456241e-02 1.456241e-02 0.9999 136.4086 573.5098)",
        27: "matrix(1 0 0 1 157.3335 603.8164)",
        26: "matrix(1 0 0 1 179.3335 623.8164)",
        25: "matrix(1 0 0 1 204.6669 628.483)",
        24: "matrix(1 0 0 1 231.3335 628.1497)",
        23: "matrix(1 0 0 1 256.3335 619.1497)",
        22: "matrix(1 0 0 1 276.3335 602.483)",
        21: "matrix(1 0 0 1 286.6669 573.1497)",
        20: "matrix(1 0 0 1 303.6327 538.667)",
        19: "matrix(1 0 0 1 322.983 495.5432)",
        18: "matrix(1 0 0 1 325.1251 449.1686)",
        17: "matrix(1 0 0 1 324.0004 402.1405)",
        16: "matrix(1 0 0 1 312.8534 324.1021)",
        15: "matrix(1 0 0 1 315.3335 275.3333)",
        14: "matrix(1 0 0 1 311.3335 236)",
        13: "matrix(1 0 0 1 300.3335 200.6667)",
        12: "matrix(1 0 0 1 286.6669 172)",
        11: "matrix(1 0 0 1 270.2269 142.439)",
        10: "matrix(1 0 0 1 247.5099 118.9722)",
        9: "matrix(1 0 0 1 227.8432 112.9722)",
        8: "matrix(1 0 0 1 200.1766 112.9722)",
        7: "matrix(1 0 0 1 170.5099 117.6388)",
        6: "matrix(1 0 0 1 148.6667 134.167)",
        5: "matrix(1 0 0 1 131.3605 164.8335)",
        4: "matrix(1 0 0 1 119.3927 195.6387)",
        3: "matrix(1 0 0 1 103.8631 234.4391)",
        2: "matrix(1 0 0 1 96.2504 275.9999)",
        1: "matrix(1 0 0 1 93.9767 324.769)"
    };
    return transforms[num];
}
