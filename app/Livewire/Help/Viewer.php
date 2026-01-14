<?php

namespace App\Livewire\Help;

use Livewire\Component;
use Illuminate\Support\Facades\File;

class Viewer extends Component
{
    public string $key;
    public array $toc = [];
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
       TOC laden & validieren
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

        foreach ($raw as $title => $group) {
            $clean = [
                'roles' => $group['roles'] ?? null,
                'items' => [],
            ];

            foreach ($group['items'] ?? [] as $item) {
                $clean['items'][] = [
                    'title'  => $item['title'] ?? null,
                    'roles'  => $item['roles'] ?? null,
                    'routes' => $item['routes'] ?? [],
                    'page'   => $item['page'] ?? null,
                ];
            }

            $toc[$title] = $clean;
        }

        $this->toc = $toc;
    }

    /* ============================================================
       Mapping: Route → page
       ============================================================ */
    protected function resolveKey(): void
    {
        foreach ($this->toc as $group) {
            foreach ($group['items'] as $entry) {

                // mehrere Routen?
                foreach ($entry['routes'] ?? [] as $r) {
                    if ($r === $this->key) {
                        $this->key = $entry['page'] ?? $entry['routes'][0];
                        return;
                    }
                }

                // direkter Page-Fall?
                if (($entry['page'] ?? null) === $this->key) {
                    return;
                }
            }
        }
    }

    /* ============================================================
       Inhalt laden
       ============================================================ */
    protected function loadContent(string $key): void
    {
        $file = resource_path("help/{$key}.html");

        if (!file_exists($file)) {
            $this->notFound = true;
            $this->html = '<p class="text-muted">Für diese Seite ist keine Hilfe verfügbar.</p>';
            return;
        }

        $html = file_get_contents($file);

        $user = auth()->user();
        $isAdmin = $user && (
            (method_exists($user, 'isAdmin') && $user->isAdmin()) ||
            (method_exists($user, 'hasRole') && $user->hasRole('admin'))
        );

        // <admin-only> block filtern
        if ($isAdmin) {
            $html = preg_replace('/<admin-only>(.*?)<\/admin-only>/s', '$1', $html);
        } else {
            $html = preg_replace('/<admin-only>.*?<\/admin-only>/s', '', $html);
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
       Suche
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

        $files = File::files(resource_path('help'));

        $user = auth()->user();
        $isAdmin = $user && (
            (method_exists($user, 'isAdmin') && $user->isAdmin()) ||
            (method_exists($user, 'hasRole') && $user->hasRole('admin'))
        );

        foreach ($files as $file) {
            if ($file->getExtension() !== 'html') continue;

            $name = $file->getFilenameWithoutExtension();
            $content = file_get_contents($file->getRealPath());

            if (!$isAdmin) {
                $content = preg_replace('/<admin-only>.*?<\/admin-only>/s', '', $content);
            }

            $plain = strip_tags($content);
            $pos = stripos($plain, $term);

            if ($pos === false) continue;

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
