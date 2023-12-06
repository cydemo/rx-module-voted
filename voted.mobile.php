<?php

class VotedMobile extends VotedView
{
	/**
	 * Support method are
	 * dispVotedContentList
	 */
	function init()
	{
		// Get the voted configuration
		$this->voted_config = $this->getConfig();
		Context::set('voted_config', $this->voted_config);

		// Set layout and skin paths
		$this->setLayoutAndTemplatePaths('M', $this->voted_config);
	}
}
/* End of file voted.mobile.php */
/* Location: ./modules/voted/voted.mobile.php */