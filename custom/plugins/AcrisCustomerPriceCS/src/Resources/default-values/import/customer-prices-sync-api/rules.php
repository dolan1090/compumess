if (!empty($value) && is_array($value)) {
    foreach ($value as $rule) {
        if (is_array($rule) && array_key_exists('id', $rule) && !empty($rule['id'])) {
            $ruleId = $rule['id'];
            $ruleRepository = $this->container->get('rule.repository');
            $id = $ruleRepository->searchIds((new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria([$ruleId])), $context)->firstId();
            if (empty($id)) {
                throw new \Exception('Rule with id "'. $ruleId .'" does not exist!');
            }
        }
    }
}

if (empty($value) || !is_array($value)) {
    $name = 'noRules';
    $value = [];
    return;
}
