<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AjudaController extends Controller
{
    /**
     * Mapeamento de seções para rótulos em português e ícones heroicon.
     */
    private const SECOES = [
        'portal'       => ['label' => 'Portal Público',         'icon' => 'globe-alt'],
        'conselheiro'  => ['label' => 'Área do Conselheiro',    'icon' => 'user-circle'],
        'gestor'       => ['label' => 'Gestão do Conselho',     'icon' => 'cog-6-tooth'],
        'admin'        => ['label' => 'Administração Municipal', 'icon' => 'building-office'],
        'lgpd'         => ['label' => 'LGPD e Privacidade',     'icon' => 'shield-check'],
    ];

    /**
     * Página inicial do centro de ajuda.
     */
    public function index()
    {
        $baseDir = resource_path('help');

        $secoes = collect(self::SECOES)->map(function ($info, $slug) use ($baseDir) {
            $dir = $baseDir . '/' . $slug;
            $topicos = [];

            if (File::isDirectory($dir)) {
                foreach (File::files($dir) as $file) {
                    $frontmatter = $this->parseFrontmatter($file->getContents());
                    $topicos[] = [
                        'slug'        => $file->getFilenameWithoutExtension(),
                        'title'       => $frontmatter['title'] ?? Str::title(str_replace('-', ' ', $file->getFilenameWithoutExtension())),
                        'description' => $frontmatter['description'] ?? '',
                    ];
                }
            }

            return [
                'slug'    => $slug,
                'label'   => $info['label'],
                'icon'    => $info['icon'],
                'topicos' => $topicos,
            ];
        })->values();

        // Build flat search index for client-side search
        $topicosBusca = [];
        foreach ($secoes as $s) {
            foreach ($s['topicos'] as $t) {
                $topicosBusca[] = [
                    'title'       => $t['title'],
                    'description' => $t['description'],
                    'secao'       => $s['slug'],
                    'secaoLabel'  => $s['label'],
                    'url'         => route('ajuda.topico', [$s['slug'], $t['slug']]),
                ];
            }
        }

        return view('ajuda.index', compact('secoes', 'topicosBusca'));
    }

    /**
     * Página de um tópico específico.
     */
    public function topico(string $secao, string $topico)
    {
        abort_unless(array_key_exists($secao, self::SECOES), 404);

        $path = resource_path("help/{$secao}/{$topico}.md");
        abort_unless(File::exists($path), 404);

        $raw         = File::get($path);
        $frontmatter = $this->parseFrontmatter($raw);
        $body        = $this->stripFrontmatter($raw);
        $html        = $this->renderMarkdown($body);

        $secaoInfo = self::SECOES[$secao];

        // Tópicos da mesma seção para navegação lateral
        $dir = resource_path("help/{$secao}");
        $outrosTopicos = [];
        if (File::isDirectory($dir)) {
            foreach (File::files($dir) as $file) {
                $fm = $this->parseFrontmatter($file->getContents());
                $outrosTopicos[] = [
                    'slug'  => $file->getFilenameWithoutExtension(),
                    'title' => $fm['title'] ?? Str::title(str_replace('-', ' ', $file->getFilenameWithoutExtension())),
                    'ativo' => $file->getFilenameWithoutExtension() === $topico,
                ];
            }
        }

        // Flat search index for layout search widget
        $topicosBusca = [];
        foreach (self::SECOES as $sSlug => $sInfo) {
            $sDir = resource_path("help/{$sSlug}");
            if (!File::isDirectory($sDir)) continue;
            foreach (File::files($sDir) as $file) {
                $fm = $this->parseFrontmatter($file->getContents());
                $tSlug = $file->getFilenameWithoutExtension();
                $topicosBusca[] = [
                    'title'       => $fm['title'] ?? Str::title(str_replace('-', ' ', $tSlug)),
                    'description' => $fm['description'] ?? '',
                    'secao'       => $sSlug,
                    'secaoLabel'  => $sInfo['label'],
                    'url'         => route('ajuda.topico', [$sSlug, $tSlug]),
                ];
            }
        }

        return view('ajuda.topico', [
            'secao'         => $secao,
            'secaoLabel'    => $secaoInfo['label'],
            'secaoIcon'     => $secaoInfo['icon'],
            'topico'        => $topico,
            'title'         => $frontmatter['title'] ?? Str::title(str_replace('-', ' ', $topico)),
            'description'   => $frontmatter['description'] ?? '',
            'html'          => $html,
            'outrosTopicos' => $outrosTopicos,
            'todasSecoes'   => self::SECOES,
            'topicosBusca'  => $topicosBusca,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function parseFrontmatter(string $content): array
    {
        if (!str_starts_with(ltrim($content), '---')) {
            return [];
        }

        preg_match('/^---\s*\n(.*?)\n---\s*\n/s', ltrim($content), $matches);

        if (empty($matches[1])) {
            return [];
        }

        $data = [];
        foreach (explode("\n", $matches[1]) as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $data[trim($key)] = trim(trim($value), '"');
            }
        }

        return $data;
    }

    private function stripFrontmatter(string $content): string
    {
        if (!str_starts_with(ltrim($content), '---')) {
            return $content;
        }

        return preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', ltrim($content)) ?? $content;
    }

    /**
     * Renderização simples de Markdown para HTML sem dependência externa.
     * Suporta: headings, bold, italic, code, links, ul, ol, blockquote, hr, tables.
     */
    private function renderMarkdown(string $md): string
    {
        $html = e($md);

        // Headings
        $html = preg_replace('/^######\s+(.+)$/m', '<h6 class="text-sm font-semibold mt-4 mb-1 text-slate-800">$1</h6>', $html);
        $html = preg_replace('/^#####\s+(.+)$/m',  '<h5 class="text-base font-semibold mt-4 mb-1 text-slate-800">$1</h5>', $html);
        $html = preg_replace('/^####\s+(.+)$/m',   '<h4 class="text-lg font-semibold mt-5 mb-2 text-slate-800">$1</h4>', $html);
        $html = preg_replace('/^###\s+(.+)$/m',    '<h3 class="text-xl font-semibold mt-6 mb-2 text-slate-900">$1</h3>', $html);
        $html = preg_replace('/^##\s+(.+)$/m',     '<h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 border-b pb-2">$1</h2>', $html);
        $html = preg_replace('/^#\s+(.+)$/m',      '<h1 class="text-3xl font-bold mt-6 mb-4 text-slate-900">$1</h1>', $html);

        // Blockquotes
        $html = preg_replace('/^&gt;\s+(.+)$/m', '<blockquote class="border-l-4 border-blue-400 bg-blue-50 pl-4 py-2 my-4 text-slate-700 italic text-sm rounded-r">$1</blockquote>', $html);

        // HR
        $html = preg_replace('/^---+$/m', '<hr class="my-6 border-slate-200">', $html);

        // Bold and italic
        $html = preg_replace('/\*\*\*(.+?)\*\*\*/', '<strong><em>$1</em></strong>', $html);
        $html = preg_replace('/\*\*(.+?)\*\*/',     '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/',          '<em>$1</em>', $html);

        // Inline code
        $html = preg_replace('/`([^`]+)`/', '<code class="bg-slate-100 text-slate-800 px-1 py-0.5 rounded text-sm font-mono">$1</code>', $html);

        // Links [text](url)
        $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" class="text-blue-600 underline hover:text-blue-800">$1</a>', $html);

        // Tables
        $html = $this->renderTables($html);

        // Lists — must run before paragraph conversion
        $html = $this->renderLists($html);

        // Paragraphs — wrap non-tagged lines
        $lines = explode("\n", $html);
        $result = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                $result[] = '';
                continue;
            }
            // Skip lines that are already HTML tags
            if (preg_match('/^<(h[1-6]|p|ul|ol|li|blockquote|hr|table|thead|tbody|tr|th|td|div|code)/', $trimmed)) {
                $result[] = $trimmed;
                continue;
            }
            $result[] = '<p class="my-3 text-slate-700 leading-relaxed">' . $trimmed . '</p>';
        }

        return implode("\n", $result);
    }

    private function renderLists(string $html): string
    {
        // Unordered lists
        $html = preg_replace_callback('/(?:^- .+\n?)+/m', function ($m) {
            $items = preg_replace('/^- (.+)$/m', '<li class="ml-4 list-disc text-slate-700">$1</li>', $m[0]);
            return '<ul class="my-3 space-y-1">' . $items . '</ul>';
        }, $html);

        // Ordered lists
        $html = preg_replace_callback('/(?:^\d+\. .+\n?)+/m', function ($m) {
            $items = preg_replace('/^\d+\. (.+)$/m', '<li class="ml-4 list-decimal text-slate-700">$1</li>', $m[0]);
            return '<ol class="my-3 space-y-1">' . $items . '</ol>';
        }, $html);

        return $html;
    }

    private function renderTables(string $html): string
    {
        return preg_replace_callback('/(?:^\|.+\|\s*\n)+/m', function ($m) {
            $lines = array_filter(array_map('trim', explode("\n", trim($m[0]))));
            $rows = array_values($lines);

            if (count($rows) < 2) {
                return $m[0];
            }

            $parseRow = fn($line) => array_map('trim', array_slice(explode('|', $line), 1, -1));

            $headers = $parseRow($rows[0]);
            // $rows[1] is the separator line (---|---)

            $thead = '<thead><tr>' . implode('', array_map(
                fn($h) => '<th class="px-4 py-2 text-left text-xs font-semibold text-slate-600 uppercase bg-slate-100 border border-slate-200">' . $h . '</th>',
                $headers
            )) . '</tr></thead>';

            $tbodyRows = '';
            for ($i = 2; $i < count($rows); $i++) {
                $cells = $parseRow($rows[$i]);
                $tbodyRows .= '<tr class="even:bg-slate-50">' . implode('', array_map(
                    fn($c) => '<td class="px-4 py-2 text-sm text-slate-700 border border-slate-200">' . $c . '</td>',
                    $cells
                )) . '</tr>';
            }

            return '<div class="overflow-x-auto my-4"><table class="min-w-full border border-slate-200 rounded-lg overflow-hidden">' . $thead . '<tbody>' . $tbodyRows . '</tbody></table></div>';
        }, $html);
    }
}
