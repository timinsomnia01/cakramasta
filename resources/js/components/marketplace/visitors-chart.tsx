import { useState } from 'react';
import { formatNumber, formatShortDate } from '@/lib/format';

type Point = { date: string; visitors: number };

/**
 * Kolom pengunjung unik per hari (satu seri, jadi tanpa legenda; judul kartu menamai seri).
 * Warna memakai --viz-series-1 (didefinisikan di app.css untuk terang & gelap).
 */
export function VisitorsChart({ data }: { data: Point[] }) {
    const [active, setActive] = useState<number | null>(null);
    const [showTable, setShowTable] = useState(false);

    const width = 640;
    const height = 220;
    const pad = { top: 16, right: 8, bottom: 28, left: 36 };
    const innerW = width - pad.left - pad.right;
    const innerH = height - pad.top - pad.bottom;

    const rawMax = Math.max(1, ...data.map((d) => d.visitors));
    const step = rawMax <= 5 ? 1 : Math.pow(10, Math.floor(Math.log10(rawMax)));
    const max = Math.ceil(rawMax / step) * step;
    const ticks = [0, max / 2, max];

    const slot = innerW / Math.max(1, data.length);
    const barW = Math.min(28, slot * 0.6);
    const y = (v: number) => pad.top + innerH - (v / max) * innerH;
    const activePoint = active !== null ? data[active] : null;

    return (
        <div
            className="viz-chart"
        >
            <div className="relative">
                <svg
                    viewBox={`0 0 ${width} ${height}`}
                    className="h-auto w-full"
                    role="img"
                    aria-label="Pengunjung unik per hari, 14 hari terakhir"
                    onMouseLeave={() => setActive(null)}
                >
                    {ticks.map((t) => (
                        <g key={t}>
                            <line
                                x1={pad.left}
                                x2={width - pad.right}
                                y1={y(t)}
                                y2={y(t)}
                                className="stroke-border"
                                strokeWidth={1}
                            />
                            <text
                                x={pad.left - 8}
                                y={y(t)}
                                textAnchor="end"
                                dominantBaseline="middle"
                                className="fill-muted-foreground text-[11px]"
                            >
                                {formatNumber(t)}
                            </text>
                        </g>
                    ))}
                    {data.map((d, i) => {
                        const x = pad.left + slot * i + (slot - barW) / 2;
                        const h = Math.max(d.visitors > 0 ? 2 : 0, innerH - (y(d.visitors) - pad.top));
                        const top = pad.top + innerH - h;
                        const r = Math.min(4, barW / 2, h);

                        return (
                            <g key={d.date}>
                                {/* area hover lebih lebar dari batang */}
                                <rect
                                    x={pad.left + slot * i}
                                    y={pad.top}
                                    width={slot}
                                    height={innerH + pad.bottom}
                                    fill="transparent"
                                    onMouseEnter={() => setActive(i)}
                                    onFocus={() => setActive(i)}
                                    onBlur={() => setActive(null)}
                                    tabIndex={0}
                                    aria-label={`${formatShortDate(d.date)}: ${d.visitors} pengunjung`}
                                />
                                {h > 0 && (
                                    <path
                                        d={`M${x},${top + h} V${top + r} Q${x},${top} ${x + r},${top} H${x + barW - r} Q${x + barW},${top} ${x + barW},${top + r} V${top + h} Z`}
                                        fill="var(--viz-series-1)"
                                        opacity={active === null || active === i ? 1 : 0.55}
                                        pointerEvents="none"
                                    />
                                )}
                                {((data.length - 1 - i) % 2 === 0 || data.length < 8) && (
                                    <text
                                        x={x + barW / 2}
                                        y={height - 8}
                                        textAnchor="middle"
                                        className="fill-muted-foreground text-[11px]"
                                    >
                                        {formatShortDate(d.date)}
                                    </text>
                                )}
                            </g>
                        );
                    })}
                </svg>
                {activePoint && active !== null && (
                    <div
                        className="pointer-events-none absolute -translate-x-1/2 -translate-y-full rounded-md border bg-popover px-2.5 py-1.5 text-xs text-popover-foreground shadow-md"
                        style={{
                            left: `${((pad.left + slot * active + slot / 2) / width) * 100}%`,
                            top: `${(y(activePoint.visitors) / height) * 100}%`,
                        }}
                    >
                        <p className="text-muted-foreground">
                            {formatShortDate(activePoint.date)}
                        </p>
                        <p className="font-medium tabular-nums">
                            {formatNumber(activePoint.visitors)} pengunjung
                        </p>
                    </div>
                )}
            </div>
            <button
                type="button"
                onClick={() => setShowTable((v) => !v)}
                className="mt-2 text-xs text-muted-foreground underline-offset-4 hover:underline"
            >
                {showTable ? 'Sembunyikan tabel' : 'Lihat sebagai tabel'}
            </button>
            {showTable && (
                <table className="mt-2 w-full text-sm">
                    <thead>
                        <tr className="border-b text-left text-xs text-muted-foreground">
                            <th className="py-1 font-medium">Tanggal</th>
                            <th className="py-1 text-right font-medium">Pengunjung</th>
                        </tr>
                    </thead>
                    <tbody>
                        {data.map((d) => (
                            <tr key={d.date} className="border-b last:border-0">
                                <td className="py-1">{formatShortDate(d.date)}</td>
                                <td className="py-1 text-right tabular-nums">{formatNumber(d.visitors)}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}
        </div>
    );
}
