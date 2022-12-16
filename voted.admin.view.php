<?php

/**
 * 추천 글/댓글
 * Copyright (c) 윤삼
 * Generated with https://www.poesis.org/tools/modulegen/
 */
class VotedAdminView extends Voted
{
	/**
	 * 초기화
	 */
	public function init()
	{
		// 관리자 화면 템플릿 경로 지정
		$this->setTemplatePath($this->module_path . 'tpl');
	}
	
	/**
	 * 관리자 설정 화면 예제
	 */
	public function dispVotedAdminConfig()
	{
		// 현재 설정 상태 불러오기
		$config = $this->getConfig();
		
		// Context에 세팅
		Context::set('voted_config', $config);

		// 레이아웃 목록
		Context::set('layout_list', getModel('layout')->getLayoutList());
		Context::set('mlayout_list', getModel('layout')->getLayoutList(0, 'M'));
		
		// 스킨 목록
		Context::set('skin_list', getModel('module')->getSkins($this->module_path));
		Context::set('mskin_list', getModel('module')->getSkins($this->module_path, 'm.skins'));

		// 스킨 파일 지정
		$this->setTemplateFile('config');
	}
}
