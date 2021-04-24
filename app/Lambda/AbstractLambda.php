<?php


namespace App\Lambda;


abstract class AbstractLambda
{
    /**
     * @param array $event
     * @return string|null
     */
    protected function getMessageFromEvent(array $event): ?string
    {
        $records = $event['Records'] ?? [];
        foreach ($records as $record) {
            $sns = $record['Sns'] ?? [];
            return $sns['Message'] ?? '';
        }

        return null;
    }
}
