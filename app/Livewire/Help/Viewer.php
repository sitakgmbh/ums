<?php

namespace App\Livewire\Help;

use Livewire\Component;
use Illuminate\Support\Facades\File;

class Viewer extends Component
{
    public string $key;
    public array $toc = [];
    public array $pageRoles = [];
    public string $html = '';
    public bool $notFound = false;

    public string $query = '';
    public array $results = [];

    public function mount(string $key): void
    {
        $this->key = $key;
        $this->loadToc();
        $this->resolveKey();
        $this->loadContent($this->key);
    }

    /* ============================================================
       TOC laden + Rollenmapping aufbauen
       ============================================================ */
    protected function loadToc(): void
    {
        $path = resource_path('help/toc.json');

        if (!file_exists($path)) {
            $this->toc = [];
            return;
        }

        $raw = json_decode(file_get_contents($path), true) ?? [];
        $toc = [];
        $this->pageRoles = [];

        foreach ($raw as $title => $group) {
            $clean = [
                'roles' => $group['roles'] ?? null,
                'items' => [],
            ];

            foreach ($group['items'] ?? [] as $item) {
                $entry = [
                    'title'  => $item['title'] ?? null,
                    'roles'  => $item['roles'] ?? null,
                    'routes' => $item['routes'] ?? [],
                    'page'   => $item['page'] ?? null,
                ];

                // Rollenmapping für Suche + Zugriff
                if ($entry['page']) {
                    $this->pageRoles[$entry['page']] = $entry['roles'] ?? null;
                }

                $clean['items'][] = $entry;
            }

            $toc[$title] = $clean;
        }

        $this->toc = $toc;
    }

    /* ============================================================
       Mapping Route → page
       ============================================================ */
    protected function resolveKey(): void
    {
        foreach ($this->toc as $group) {
            foreach ($group['items'] as $entry) {

                foreach ($entry['routes'] ?? [] as $r) {
                    if ($r === $this->key) {
                        $this->key = $entry['page'] ?? $entry['routes'][0];
                        return;
                    }
                }

                if (($entry['page'] ?? null) === $this->key) {
                    return;
                }
            }
        }
    }

    /* ============================================================
       Inhalt laden + Rollen pruefen
       ============================================================ */
protected function loadContent(string $key): void
{
    $roles = $this->pageRoles[$key] ?? null;

    if (!$this->hasRoleAccess($roles)) {
        $this->notFound = true;
        $this->html = '<p class="text-muted">Kein Zugriff.</p>';
        return;
    }

    $file = resource_path("help/{$key}.html");

    if (!file_exists($file)) {
        $this->notFound = true;
        $this->html = '<p class="text-muted">Fuer diese Seite ist keine Hilfe verfuegbar.</p>';
        return;
    }

    $html = file_get_contents($file);

    $user = auth()->user();
    $isAdmin = $user && (
        (method_exists($user, 'isAdmin') && $user->isAdmin()) ||
        (method_exists($user, 'hasRole') && $user->hasRole('admin'))
    );

    //
    // admin-only: Admin bekommt Inhalt, Non-Admin nix
    //
    if ($isAdmin) {
        $html = preg_replace('/<admin-only>(.*?)<\/admin-only>/s', '$1', $html);
    } else {
        $html = preg_replace('/<admin-only>.*?<\/admin-only>/s', '', $html);
    }

    //
    // admin-note: Admin bekommt Tag + Inhalt, Non-Admin nix
    //
    if (!$isAdmin) {
        $html = preg_replace('/<admin-note>.*?<\/admin-note>/s', '', $html);
    }

    $this->html = $html;
}



    /* ============================================================
       Rollenprüfung
       ============================================================ */
    protected function hasRoleAccess(?array $roles): bool
    {
        if (!$roles || empty($roles)) return true;

        $user = auth()->user();
        if (!$user) return false;

        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole($roles);
        }

        if (in_array('admin', $roles) && method_exists($user, 'isAdmin')) {
            return $user->isAdmin();
        }

        return false;
    }

    /* ============================================================
       Suche (mit Rollenfilter)
       ============================================================ */
    public function updatedQuery(): void
    {
        $this->performSearch();
    }

    public function submitSearch(): void
    {
        $this->performSearch();
    }

public function performSearch(): bool
{
    $this->results = [];
    $term = trim($this->query);

    if (strlen($term) < 2) {
        return false;
    }

    $files = collect(File::files(resource_path('help')))
        ->filter(fn($f) => $f->getExtension() === 'html')
        ->filter(function ($f) {
            $name = $f->getFilenameWithoutExtension();
            return array_key_exists($name, $this->pageRoles);
        });

    $user = auth()->user();
    $isAdmin = $user && (
        (method_exists($user, 'isAdmin') && $user->isAdmin()) ||
        (method_exists($user, 'hasRole') && $user->hasRole('admin'))
    );

    foreach ($files as $file) {
        $name = $file->getFilenameWithoutExtension();
        $roles = $this->pageRoles[$name] ?? null;

        // Page Rollen pruefen
        if (!$this->hasRoleAccess($roles)) {
            continue;
        }

        $content = file_get_contents($file->getRealPath());

        // Admin-Blöcke fuer Nichtadmins entfernen
        if (!$isAdmin) {
            $patterns = [
                '/<admin-only>.*?<\/admin-only>/s',
                '/<admin-note>.*?<\/admin-note>/s',
            ];

            foreach ($patterns as $pattern) {
                $content = preg_replace($pattern, '', $content);
            }
        }

        $plain = strip_tags($content);
        $pos = stripos($plain, $term);

        if ($pos === false) {
            continue;
        }

        $start = max(0, $pos - 80);
        $excerpt = substr($plain, $start, strlen($term) + 160);

        $excerpt = preg_replace(
            '/' . preg_quote($term, '/') . '/i',
            '<mark>$0</mark>',
            $excerpt
        );

        $this->results[] = [
            'key'     => $name,
            'excerpt' => trim($excerpt),
        ];
    }

    return count($this->results) > 0;
}

    /* ============================================================
       Navigation
       ============================================================ */
    public function goTo(string $target)
    {
        if (str_starts_with($target, 'page:')) {
            $page = substr($target, 5);
            return redirect()->route('help.viewer', ['key' => $page]);
        }

        if (str_starts_with($target, 'route:')) {
            $route = substr($target, 6);
            return redirect()->route('help.viewer', ['key' => $route]);
        }

        return redirect()->route('help.viewer', ['key' => $target]);
    }

    public function render()
    {
        return view('livewire.help.viewer')
            ->layout('layouts.help', [
                'pageTitle' => 'UMS Hilfe',
            ]);
    }
}
