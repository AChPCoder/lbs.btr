<?php

namespace Dev\Site\Agents;


class Iblock
{
    private static function agentLog($msg)
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log_iblock_logging_agent.txt', $msg . "\n", FILE_APPEND);
    }

    public static function clearOldLogs()
    {
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            self::agentLog('AgentStarted at ' . date('Y-m-d H:i:s'));
            $logIBlockId = \Dev\Site\Handlers\Iblock::getLogIBlockId();

            $limit = 10;

            $rsLogs = \CIBlockElement::GetList(
                ['DATE_ACTIVE_FROM' => 'DESC'], // sort
                [ // filter
                    'IBLOCK_ID' => $logIBlockId,
                ],
                false,
                ['nTopCount' => $limit], // navStart
                ['ID', 'IBLOCK_ID'] // select
            );

            $excluded_ids = [];

            while ($arLog = $rsLogs->Fetch()) {
                $excluded_ids[] = $arLog['ID'];
            }

            $is_ok = !(count($excluded_ids) < $limit);
            if ($is_ok) {
                self::agentLog('AgentFoundItemsAtLeast ' . $limit . ' at ' . date('Y-m-d H:i:s'));
                $rsLogs = \CIBlockElement::GetList(
                    ['DATE_ACTIVE_FROM' => 'ASC'], // sort
                    [ // filter
                        'IBLOCK_ID' => $logIBlockId,
                        '!ID' => $excluded_ids
                    ],
                    false,
                    false, // navStart
                    ['ID', 'IBLOCK_ID'] // select
                );

                $countDel = 0;
                while ($arLogDel = $rsLogs->Fetch()) {
                    \CIBlockElement::Delete($arLogDel['ID']);
                    $countDel++;
                }
                self::agentLog('AgentDeletedItemsCount ' . $countDel . ' at ' . date('Y-m-d H:i:s'));
            }
            self::agentLog('AgentEnded at ' . date('Y-m-d H:i:s'));
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }

    public static function example()
    {
        global $DB;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockId = \Only\Site\Helpers\IBlock::getIblockID('QUARRIES_SEARCH', 'SYSTEM');
            $format = $DB->DateFormatToPHP(\CLang::GetDateFormat('SHORT'));
            $rsLogs = \CIBlockElement::GetList(['TIMESTAMP_X' => 'ASC'], [
                'IBLOCK_ID' => $iblockId,
                '<TIMESTAMP_X' => date($format, strtotime('-1 months')),
            ], false, false, ['ID', 'IBLOCK_ID']);
            while ($arLog = $rsLogs->Fetch()) {
                \CIBlockElement::Delete($arLog['ID']);
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}
