@props(['html'])

@once
    <style>
        .chapter-body {
            color: #0f172a;
            text-align: justify;
            text-justify: inter-word;
            hyphens: auto;
            -webkit-hyphens: auto;
        }
        .chapter-body > *:first-child { margin-top: 0; }
        .chapter-body > *:last-child { margin-bottom: 0; }
        .chapter-body p,
        .chapter-body li,
        .chapter-body td,
        .chapter-body th,
        .chapter-body blockquote,
        .chapter-body h3,
        .chapter-body h4,
        .chapter-body h5,
        .chapter-body h6 {
            text-align: justify !important;
        }
        .chapter-body p { margin: 0.85em 0; }
        .chapter-body h3 { font-size: 1.2rem; font-weight: 650; margin: 1.4em 0 0.5em; }
        .chapter-body h4, .chapter-body h5, .chapter-body h6 { font-size: 1.05rem; font-weight: 650; margin: 1.2em 0 0.4em; }
        .chapter-body ul { list-style: disc; padding-left: 1.5rem; margin: 0.85em 0; }
        .chapter-body ol { list-style: decimal; padding-left: 1.5rem; margin: 0.85em 0; }
        .chapter-body ul ul, .chapter-body ol ol, .chapter-body ul ol, .chapter-body ol ul { margin: 0.2em 0; }
        .chapter-body table { width: 100%; border-collapse: collapse; margin: 1em 0; font-size: 0.95rem; display: block; overflow-x: auto; }
        .chapter-body th, .chapter-body td { border: 1px solid #cbd5e1; padding: 0.5rem 0.7rem; vertical-align: top; }
        .chapter-body th { background: #f8fafc; font-weight: 600; }
        .chapter-body img { max-width: 100%; height: auto; margin: 0.75em 0; border-radius: 0.4rem; }
        .chapter-body a { color: #3730a3; text-decoration: underline; }
        .chapter-body strong, .chapter-body b { font-weight: 700; }
        .chapter-body em, .chapter-body i { font-style: italic; }
        .chapter-body u { text-decoration: underline; }
        .chapter-body s, .chapter-body strike { text-decoration: line-through; }
        .chapter-body blockquote { border-left: 3px solid #c7d2fe; padding-left: 1rem; color: #334155; margin: 1em 0; }
        .chapter-body pre, .chapter-body code { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 0.9em; text-align: left !important; }
        .chapter-body pre { background: #f8fafc; padding: 0.75rem; border-radius: 0.5rem; overflow: auto; }
    </style>
@endonce

<article {{ $attributes->merge(['class' => 'chapter-body rounded-xl border bg-white p-4 leading-relaxed sm:p-6', 'lang' => 'fr']) }}>
    {!! $html !!}
</article>
