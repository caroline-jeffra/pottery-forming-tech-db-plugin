<?php
declare(strict_types=1);

namespace Cjeffra\PotteryFormingTechManager;

class CustomEditor
{
	public function __construct()
	{
		add_action('init', [$this, 'addVesselPostType']);
	}

	static function addVesselPostType(): void
	{
		register_post_type('Vessel',
			[
				'labels' => [
					'name' => __('Vessels', 'textdomain'),
					'singular_name' => __('Vessel', 'textdomain'),
				],
				'public' => true,
				'has_archive' => true,
				'rewrite' => ['slug' => 'vessels'],
			]
		);
	}
}