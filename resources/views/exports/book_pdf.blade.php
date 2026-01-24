<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            /* DejaVu supports Arabic well in DomPDF */
            direction: rtl;
            text-align: right;
            padding: 20px;
            line-height: 1.6;
            color: #333;
        }

        .title-main {
            text-align: center;
            font-size: 28pt;
            margin-bottom: 50px;
            color: #1a202c;
            border-bottom: 3px double #cbd5e0;
            padding-bottom: 20px;
        }

        .chapter-title {
            font-size: 20pt;
            color: #2d3748;
            margin-top: 40px;
            border-right: 5px solid #4a5568;
            padding-right: 15px;
            background: #f7fafc;
        }

        .paragraph {
            margin-bottom: 15px;
            font-size: 14pt;
            text-align: justify;
        }

        .footnote-marker {
            vertical-align: super;
            font-size: 10pt;
            color: #b45309;
            font-weight: bold;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="title-main">{{ $title }}</div>

    @foreach($items as $item)
        <div class="chapter">
            <div class="chapter-title">{{ $item->title }}</div>
            <div class="content">
                @foreach($item->content_blocks as $block)
                    @if($block['type'] === 'paragraph')
                        <p class="paragraph">{!! $controller->renderNode($block) !!}</p>
                    @elseif($block['type'] === 'heading')
                        @php $level = ($block['attrs']['level'] ?? 1) + 2; @endphp
                        <h{{ $level }} class="heading-{{ $level }}">{!! $controller->renderNode($block) !!}</h{{ $level }}>
                    @elseif($block['type'] === 'bulletList')
                        <ul>
                            @foreach($block['content'] as $li)
                                <li>{!! $controller->renderNode($li) !!}</li>
                            @endforeach
                        </ul>
                    @elseif($block['type'] === 'orderedList')
                        <ol>
                            @foreach($block['content'] as $li)
                                <li>{!! $controller->renderNode($li) !!}</li>
                            @endforeach
                        </ol>
                    @elseif($block['type'] === 'blockquote')
                        <blockquote>{!! $controller->renderNode($block) !!}</blockquote>
                    @endif
                @endforeach
            </div>
        </div>
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>

</html>