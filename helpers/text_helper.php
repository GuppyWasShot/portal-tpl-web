<?php

if (!function_exists('slugify_text')) {
    function slugify_text(string $text, int $maxLength = 100): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');

        if ($text === '') {
            $text = 'item';
        }

        return substr($text, 0, $maxLength);
    }
}

if (!function_exists('generate_unique_slug')) {
    function generate_unique_slug(mysqli $conn, string $table, string $slugColumn, string $baseText, string $idColumn = 'id', ?int $excludeId = null, int $maxLength = 100): string
    {
        $base = slugify_text($baseText, $maxLength);
        $slug = $base;
        $suffix = 1;

        while (slug_exists_in_table($conn, $table, $slugColumn, $idColumn, $slug, $excludeId)) {
            $suffix++;
            $suffixStr = '-' . $suffix;
            $slug = substr($base, 0, $maxLength - strlen($suffixStr)) . $suffixStr;
        }

        return $slug;
    }
}

if (!function_exists('slug_exists_in_table')) {
    function slug_exists_in_table(mysqli $conn, string $table, string $slugColumn, string $idColumn, string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM {$table} WHERE {$slugColumn} = ? AND {$idColumn} <> ?");
            $stmt->bind_param("si", $slug, $excludeId);
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM {$table} WHERE {$slugColumn} = ?");
            $stmt->bind_param("s", $slug);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['total'] > 0;
    }
}

