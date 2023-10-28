if (!empty($value) && is_string($value)) {
    $value = trim($value);
    if ($value !== 'ifEmptyUseOriginal' && $value !== 'ifBothEmptyUseNormalPrice') {
        $value = 'replace';
    }
}
