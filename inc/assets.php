<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'yw_enqueue_assets' );

function yw_enqueue_assets() {
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();

	wp_enqueue_style(
		'generatepress-child-style',
		$uri . '/style.css',
		[ 'generatepress-style' ],
		filemtime( $dir . '/style.css' )
	);

}

/*
 * 다크모드 깜빡임 방지 스크립트 — <head> 최상단(priority 1)에서 즉시 실행.
 *
 * 테마 결정 우선순위:
 *   1순위: localStorage["yw-theme"] — 사용자가 토글 버튼으로 직접 선택한 값
 *   2순위: prefers-color-scheme     — 저장값 없을 때 시스템 모드 따름
 *
 * CSS는 <html data-theme="dark"> 속성으로 제어 (style.css의 [data-theme="dark"] 선택자).
 */
/*
 * WordPress 기본 이모지 자동변환 비활성화
 *
 * WordPress는 기본적으로 이모지 문자를 <img> 태그로 변환하고
 * 관련 스크립트/CSS를 <head>에 삽입함.
 * 이모지를 직접 텍스트로 제어하기 위해 해당 기능을 모두 제거.
 *
 * init 훅 안에서 실행해야 WordPress 코어가 등록한 뒤 제거 가능.
 */
add_action( 'init', function () {
	remove_action( 'wp_head',         'print_emoji_detection_script', 7 ); // <head> 이모지 감지 스크립트
	remove_action( 'wp_print_styles', 'print_emoji_styles' );               // 이모지 CSS
	remove_filter( 'the_content',     'wp_staticize_emoji' );               // 본문 이모지 → img 변환
	remove_filter( 'comment_text',    'wp_staticize_emoji' );               // 댓글 이모지 → img 변환
} );

add_action( 'wp_head', 'yw_theme_init_script', 1 );

function yw_theme_init_script() {
	echo '<script>!function(){' .
		'var s=localStorage.getItem("yw-theme"),' .          // 1순위: 저장된 사용자 선택
		'p=window.matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light",' . // 2순위: 시스템 모드
		't=s||p;' .                                          // 저장값 우선, 없으면 시스템값
		't==="dark"&&document.documentElement.setAttribute("data-theme","dark")' .
	'}();</script>' . "\n";
}
