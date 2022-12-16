<?php

/**
 * 추천 글/댓글
 * Copyright (c) 윤삼
 * Generated with https://www.poesis.org/tools/modulegen/
 */
class VotedController extends Voted
{
	/**
	 * 트리거: 회원정보 페이지에 탭 삽입
	 */
	public function triggerAddMemberMenu()
	{
		$voted_config = $this->getConfig();
		if ( in_array($voted_config->general_config, ['D', 'C', 'A']) )
		{
			$logged_info = Context::get('logged_info');
			if ( !Context::get('is_logged') )
			{
				return;
			}
			
			if ( $voted_config->general_config === 'D' )
			{
				$message = 'msg_view_voted_document';
			}
			else if ( $voted_config->general_config === 'C' )
			{
				$message = 'msg_view_voted_comment';
			}
			else if ( $voted_config->general_config === 'A' )
			{
				$message = 'msg_view_voted_content';
			}

			$oMemberController = getController('member');
			$oMemberController->addMemberMenu('dispVotedContentList', $message);
		}
	}

	/**
	 * 트리거: 문서/댓글의 추천 발생시 반영
	 */
	public function triggerVoteUp($obj)
	{
		if ( $obj->update_target !== 'voted_count' || $obj->point < 0 )
		{
			return;
		}

		$args = new stdClass;
		$args->content_type = isset($obj->comment_srl) ? 2: 1;
		$args->target_srl = isset($obj->comment_srl) ? $obj->comment_srl: $obj->document_srl;
		$args->member_srl = $this->user->member_srl;
		$args->point = $obj->point;

		// begin transaction
		$oDB = DB::getInstance();
		$oDB->begin();

		// Update the voted count
		$output = executeQuery('voted.insertVotedContentLog', $args);
		if ( !$output->toBool() )
		{
			$oDB->rollback();
			return $output;
		}
		
		$oDB->commit();
	}

	/**
	 * 트리거: 문서/댓글의 추천 취소시 반영
	 */
	public function triggerVoteCancel($obj)
	{
		$is_del_content = $obj->content ? true: false;

		// 댓글 삭제 실행시 => 휴지통으로 이동하는 경우면 댓글 추천 기록 보존
		if ( $is_del_content )
		{
			if ( isset($obj->comment_srl) && $obj->isMoveToTrash )
			{
				return;
			}
		}
		// 일반적인 취소의 경우 => 추천수에 관한 커맨드가 아니면 반환
		else
		{
			if ( $obj->update_target !== 'voted_count' || $obj->point < 0 )
			{
				return;
			}
		}

		$args = new stdClass;
		$args->content_type = isset($obj->comment_srl) ? 2: 1;
		$args->target_srl = isset($obj->comment_srl) ? $obj->comment_srl: $obj->document_srl;

		// 문서/댓글 삭제 실행시 => 해당 콘텐츠 관련 추천 기록을 모두 삭제
		// 일반적인 취소의 경우 => 취소자 회원번호에 해당하는 콘텐츠 추천 기록만 삭제
		$args->member_srl = $is_del_content ? null: $this->user->member_srl;

		// begin transaction
		$oDB = DB::getInstance();
		$oDB->begin();

		// Update the voted count
		$output = executeQuery('voted.deleteVotedContentLog', $args);
		if ( !$output->toBool() )
		{
			$oDB->rollback();
			return $output;
		}
		
		$oDB->commit();
	}
}
