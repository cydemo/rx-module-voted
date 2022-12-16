<?php

/**
 * 추천 글/댓글
 * Copyright (c) 윤삼
 * Generated with https://www.poesis.org/tools/modulegen/
 */
class VotedAdminController extends Voted
{
	/**
	 * 관리자 설정 저장 액션 예제
	 */
	public function procVotedAdminInsertConfig()
	{
		// 현재 설정 상태 불러오기
		$config = $this->getConfig();

		// 제출받은 데이터 불러오기
		$vars = Context::getRequestVars();

		$config_names = array(
			'general_config',
			'layout_srl',
			'skin',
			'mlayout_srl',
			'mskin',
		);

		foreach ( $config_names as $val )
		{
			$config->{$val} = $vars->{$val};
		}

		// 변경된 설정을 저장
		$output = $this->setConfig($config);
		if ( !$output->toBool() )
		{
			return $output;
		}

		// 설정 화면으로 리다이렉트
		$this->setMessage('success_registed');
		$this->setRedirectUrl(Context::get('success_return_url'));
	}

	function procVotedAdminImportLog()
	{
		if ( $this->user->is_admin !== 'Y' )
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}

		$oDB = DB::getInstance();

		$query = "INSERT IGNORE INTO voted_content_log (content_type, target_srl, member_srl, ipaddress, regdate, point)
			SELECT ?, document_voted_log.document_srl, document_voted_log.member_srl, document_voted_log.ipaddress, document_voted_log.regdate as voted_date, document_voted_log.point
			FROM document_voted_log WHERE document_voted_log.point >= ?";
		$stmt = $oDB->query($query, 1, 0);
		$result = $stmt->fetchAll();
		$document_vote_log_count = $stmt->rowCount();

		$query = "INSERT IGNORE INTO voted_content_log (content_type, target_srl, member_srl, ipaddress, regdate, point)
			SELECT ?, comment_voted_log.comment_srl, comment_voted_log.member_srl, comment_voted_log.ipaddress, comment_voted_log.regdate as voted_date, comment_voted_log.point
			FROM comment_voted_log WHERE comment_voted_log.point >= ?";
		$stmt = $oDB->query($query, 2, 0);
		$result = $stmt->fetchAll();
		$comment_vote_log_count = $stmt->rowCount();
		
		$this->add('document_vote_log_count', $document_vote_log_count);
		$this->add('comment_vote_log_count', $comment_vote_log_count);
	}
}
