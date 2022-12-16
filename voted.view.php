<?php

/**
 * 추천 글/댓글
 * Copyright (c) 윤삼
 * Generated with https://www.poesis.org/tools/modulegen/
 */
class VotedView extends Voted
{
	/**
	 * 초기화
	 */
	public function init()
	{
		$this->voted_config = $this->getConfig();
		Context::set('config', $this->voted_config);

		if ( !Mobile::isMobileCheckByAgent() )
		{
			// 스킨 경로 지정
			$this->setTemplatePath($this->module_path . 'skins/' . ($this->voted_config->skin ?: 'default'));

			// 레이아웃
			if ( $this->voted_config->layout_srl )
			{
				$this->module_info->layout_srl = $this->voted_config->layout_srl;
			}
		}
		else
		{
			// 모바일 스킨 경로 지정
			$mskin = $this->voted_config->mskin;
			if ( !$mskin )
			{
				$template_path = sprintf('%sm.skins/%s/', $this->module_path, 'default');
			}
			else if ( $mskin === '/USE_RESPONSIVE/' )
			{
				$template_path = sprintf('%sskins/%s/', $this->module_path, $this->voted_config->skin);
				if ( !is_dir($template_path) || !$this->voted_config->skin )
				{
					$template_path = sprintf('%sskins/%s/', $this->module_path, 'default');
				}
			}
			else
			{
				$template_path = sprintf('%sm.skins/%s', $this->module_path, $mskin);
			}
			$this->setTemplatePath($template_path);

			// 모바일 레이아웃
			$mlayout_srl = $this->voted_config->mlayout_srl;
			if ( $this->voted_config->mlayout_srl )
			{
				// PC와 동일한 반응형
				if ( $mlayout_srl < 0 )
				{
					$mlayout_srl = $this->voted_config->layout_srl;
				}

				if ( $layout_path = getModel('layout')->getLayout($mlayout_srl)->path )
				{
					$this->setLayoutPath($layout_path);
					$this->setLayoutFile('layout');
				}
			}
		}
	}

	function dispVotedContentList()
	{
		// 회원이 아니면 반환
		if ( !$this->user->member_srl )
		{
			return;
		}

		// 페이지 형식 확인 후, 허용되지 않은 형식이면 반환
		$general_config = Context::get('general_config') ?: $this->voted_config->general_config;
		if ( !$general_config || !in_array($general_config, ['D', 'C', 'A']) )
		{
			return;
		}

		$this->setTemplateFile('voted_content');

		$args = new stdClass;
		$args->member_srl = $this->user->member_srl;
		$args->list_count = $list_count = 20;
		$args->page_count = 5;
		$args->page = intval(Context::get('page')) ?: 1;

		// 검색 대상 및 검색어 처리
		if ( in_array($search_target = Context::get('search_target'), ['title', 'title_content', 'content']) )
		{
			$search_keyword = escape(trim(utf8_normalize_spaces(Context::get('search_keyword'))));

			if ( $search_target && $search_keyword )
			{
				// 보기 모드 이동에 따라 검색 결과가 자연스러울 수 있도록, 댓글 모드일 경우에도 제목 검색을 허용
				if ( $search_target === 'title_content' || $general_config === 'C' )
				{
					$args->s_title = $search_keyword;
					$args->s_content = $search_keyword;
				}
				else
				{
					$args->{'s_' . $search_target} = $search_keyword;
				}
			}
		}

		// 추천 문서 보기
		if ( $general_config === 'D' )
		{
			Context::set('voted_title', lang('msg_view_voted_document'));

			$output = executeQueryArray('voted.getVotedDocumentList', $args);
			if ( !$output->toBool() || !$result = $output->data )
			{
				Context::set('content_list', []);
				Context::set('total_count', 0);
				Context::set('total_page', 1);
				Context::set('page', 1);
				Context::set('page_navigation', new PageHandler(0,0,1,5));
				return;
			}

			$output->data = array();
			foreach ( $result as $key => $attribute )
			{
				if ( !isset($GLOBALS['XE_DOCUMENT_LIST'][$attribute->document_srl]) )
				{
					$oDocument = new documentItem();
					$oDocument->setAttribute($attribute, false);
				}
				$output->data[$key] = $GLOBALS['XE_DOCUMENT_LIST'][$attribute->document_srl];
			}
		}
		// 추천 댓글 보기
		else if ( $general_config === 'C' )
		{
			Context::set('voted_title', lang('msg_view_voted_comment'));

			$output = executeQueryArray('voted.getVotedCommentList', $args);
			if ( !$output->toBool() || !$result = $output->data )
			{
				Context::set('content_list', []);
				Context::set('total_count', 0);
				Context::set('total_page', 1);
				Context::set('page', 1);
				Context::set('page_navigation', new PageHandler(0,0,1,5));
				return;
			}

			$output->data = array();
			foreach ( $result as $key => $attribute )
			{
				$oComment = new commentItem();
				$oComment->setAttribute($attribute);

				$output->data[$key] = $oComment;
			}
		}
		// 추천 글/댓글 모두 보기
		else if ( $general_config === 'A' )
		{
			Context::set('voted_title', lang('msg_view_voted_content'));

			$output = executeQueryArray('voted.getVotedContentList', $args);
			if ( !$output->toBool() || !$result = $output->data )
			{
				Context::set('content_list', []);
				Context::set('total_count', 0);
				Context::set('total_page', 1);
				Context::set('page', 1);
				Context::set('page_navigation', new PageHandler(0,0,1,5));
				return;
			}

			$output->data = array();
			foreach ( $result as $key => $attribute )
			{
				$values = new stdClass;
				$values->voted_date = $result[$key]->voted_date;

				// 문서 객체를 배당
				if ( $attribute->content_type === 1 )
				{
					$values->document_srl = $result[$key]->target_srl;
					$values->module_srl = $result[$key]->document_module_srl;
					$values->member_srl = $result[$key]->document_member_srl;
					$values->nick_name = $result[$key]->document_nick_name;
					$values->title = $result[$key]->document_title;
					$values->voted_count = $result[$key]->document_voted_count;
					$values->comment_count = $result[$key]->document_comment_count;
					$values->regdate = $result[$key]->document_regdate;

					if ( !isset($GLOBALS['XE_DOCUMENT_LIST'][$values->document_srl]) )
					{
						$oDocument = new documentItem();
						$oDocument->setAttribute($values, false);
					}
					$output->data[$key] = $GLOBALS['XE_DOCUMENT_LIST'][$values->document_srl];
				}
				// 댓글 객체를 배당
				else if ( $attribute->content_type === 2 )
				{
					$values->comment_srl = $result[$key]->target_srl;
					$values->module_srl = $result[$key]->comment_module_srl;
					$values->document_srl = $result[$key]->comment_document_srl;
					$values->member_srl = $result[$key]->comment_member_srl;
					$values->nick_name = $result[$key]->comment_nick_name;
					$values->content = $result[$key]->comment_content;
					$values->voted_count = $result[$key]->comment_voted_count;
					$values->regdate = $result[$key]->comment_regdate;

					$oComment = new commentItem();
					$oComment->setAttribute($values);

					$output->data[$key] = $oComment;
				}
			}
		}

		Context::set('content_list', $output->data);
		Context::set('total_count', $output->total_count);
		Context::set('total_page', $output->total_page);
		Context::set('page', $output->page);
		Context::set('page_navigation', $output->page_navigation);

		$oSecurity = new Security();
		$oSecurity->encodeHTML('search_target', 'search_keyword');
	}
}
