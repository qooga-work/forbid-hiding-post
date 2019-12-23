<?php
/* 
Plugin Name: Forbid hiding post
Plugin URI: http://qooga.jb-jk.net/forbid-hiding-post/
Description: I forbid hiding post!
Version: 1.0
Author: QoogaKIKAKU (T.O)
Author URI: https://qooga.jb-jk.net
License: https://github.com/qooga-work/forbid-hiding-post/blob/master/LICENSE
Text Domain: forbid-hiding-post
*/

// リビジョン管理は不要
if (!defined('WP_POST_REVISIONS'))
{
	define('WP_POST_REVISIONS', false);	
}

// 自動保存を7日(604800秒)毎
if (!defined('AUTOSAVE_INTERVAL'))
{
	define('AUTOSAVE_INTERVAL', 604800);
}

// 自動保存停止
function forbid_hiding_post_print_scripts()
{
	wp_deregister_script('autosave');	// オートセーブスクリプト停止
}
add_action('wp_print_scripts','forbid_hiding_post_print_scripts');

// 自動下書を作るクイックドラフトを非表示(停止)
function forbid_hiding_post_dashboard_setup()
{
	// remove処理が動くときのみ
	if (function_exists( 'remove_meta_box' ))
	{
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	}
}
add_action('wp_dashboard_setup', 'forbid_hiding_post_dashboard_setup' );
// それでも出来る自動下書等を非公開記事に変更
function forbid_hiding_post_insert_post( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE && ! (int)$post_id )
	{
		return;
	}

	// 裏で動くステータス全て停止
	$stop_status = array(
		'auto-draft',	// 自動下書
		'inherit',		// 継承
		'trash',		// ゴミ箱
	);

	if(in_array($post->post_status,$stop_status))
	{
		$update = array(
			'ID' => (int)$post_id,
			'post_title' => "[".$post->post_status."]".$post->post_title,	// タイトルに元状態追加
			'post_status' => 'private',	// 下書:draft 非公開:private
			'post_parent' => 0,			// 親指定消す
			'post_type' => 'post',	// revisionを通常投稿へ
		);
		wp_update_post($update);
	}
}
add_action( 'wp_insert_post', 'forbid_hiding_post_insert_post', 10, 2 );
