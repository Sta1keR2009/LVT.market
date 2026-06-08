<?php

/**
 * Lightweight sphinx row formatter for catalog search (no BODY column / no body SNIPPETS).
 */
class LvtSearchSphinxFormatter extends CSearchSphinxFormatter
{
	public function formatRow($r)
	{
		$sphinxTitle = isset($r['title']) ? (string)$r['title'] : '';
		$DB = CDatabase::GetModuleConnection('search');

		if ($this->sphinx->SITE_ID)
		{
			$rs = $DB->Query('
				select
					sc.ID
					,sc.MODULE_ID
					,sc.ITEM_ID
					,sc.TAGS
					,sc.PARAM1
					,sc.PARAM2
					,sc.UPD
					,sc.DATE_FROM
					,sc.DATE_TO
					,sc.URL
					,sc.CUSTOM_RANK
					,' . $DB->DateToCharFunction('sc.DATE_CHANGE') . ' as FULL_DATE_CHANGE
					,' . $DB->DateToCharFunction('sc.DATE_CHANGE', 'SHORT') . ' as DATE_CHANGE
					,scsite.SITE_ID
					,scsite.URL SITE_URL
					,sc.USER_ID
				from b_search_content sc
				INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID
				where ID = ' . (int)$r['id'] . "
				and scsite.SITE_ID = '" . $DB->ForSql($this->sphinx->SITE_ID) . "'
			");
		}
		else
		{
			$rs = $DB->Query('
				select
					sc.ID
					,sc.MODULE_ID
					,sc.ITEM_ID
					,sc.TAGS
					,sc.PARAM1
					,sc.PARAM2
					,sc.UPD
					,sc.DATE_FROM
					,sc.DATE_TO
					,sc.URL
					,sc.CUSTOM_RANK
					,' . $DB->DateToCharFunction('sc.DATE_CHANGE') . ' as FULL_DATE_CHANGE
					,' . $DB->DateToCharFunction('sc.DATE_CHANGE', 'SHORT') . ' as DATE_CHANGE
					,\'\' as SITE_ID
				from b_search_content sc
				where ID = ' . (int)$r['id'] . '
			');
		}

		$row = $rs->Fetch();
		if (!$row)
		{
			return false;
		}

		$title = $sphinxTitle !== '' ? $sphinxTitle : (string)($row['TITLE'] ?? '');
		$row['TITLE'] = $title;
		$row['TITLE_FORMATED'] = $this->buildExcerpts(htmlspecialcharsex($title));
		$row['TITLE_FORMATED_TYPE'] = 'html';
		$row['TAGS_FORMATED'] = tags_prepare($row['TAGS'], SITE_ID);
		$row['BODY_FORMATED'] = '';
		$row['BODY_FORMATED_TYPE'] = 'html';

		return $row;
	}
}
