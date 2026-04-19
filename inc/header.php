<?php
defined( 'ABSPATH' ) || exit;

/* 프론트엔드 관리자 툴바 제거 */
add_action( 'after_setup_theme', function () {
	show_admin_bar( false );
} );

/* GeneratePress 기본 헤더 제거 후 커스텀 헤더로 대체 */
add_action( 'after_setup_theme', function () {
	remove_action( 'generate_header', 'generate_construct_header' );
	add_action( 'generate_header', 'yw_render_header' );
}, 20 );

function yw_render_header() {
	$site_url  = home_url( '/' );
	$site_name = get_bloginfo( 'name' );
	?>
	<header class="yw-header" id="yw-header" role="banner">
		<div class="yw-header-inner">

			<a href="<?php echo home_url(); ?>" class="yw-header-logo">
				<?php include get_stylesheet_directory() . '/assets/img/logo.svg'; ?>
			</a>

			<nav class="yw-header-nav" aria-label="Primary navigation">
				<a href="<?php echo esc_url( home_url( '/origin' ) ); ?>" class="yw-header-nav-link">Origin</a>
				<a href="<?php echo esc_url( home_url( '/projects' ) ); ?>" class="yw-header-nav-link">Projects</a>
				<a href="<?php echo esc_url( home_url( '/log368' ) ); ?>" class="yw-header-nav-link">Log368</a>

				<button class="yw-theme-toggle" id="yw-theme-toggle" aria-label="라이트/다크 모드 전환">
					<!-- 달 아이콘 (라이트 모드일 때 표시) -->
					<svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
					</svg>
					<!-- 해 아이콘 (다크 모드일 때 표시) -->
					<svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true">
						<circle cx="12" cy="12" r="5"/>
						<line x1="12" y1="1"  x2="12" y2="3"/>
						<line x1="12" y1="21" x2="12" y2="23"/>
						<line x1="4.22" y1="4.22"  x2="5.64" y2="5.64"/>
						<line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
						<line x1="1"  y1="12" x2="3"  y2="12"/>
						<line x1="21" y1="12" x2="23" y2="12"/>
						<line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
						<line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
					</svg>
				</button>
			</nav>

		</div>
	</header>

	<script>
	(function () {
		/* Sticky 헤더 */
		var header    = document.getElementById('yw-header');
		var threshold = header ? header.offsetHeight : 60;

		function onScroll() {
			if (window.scrollY >= threshold) {
				header.classList.add('is-sticky');
			} else {
				header.classList.remove('is-sticky');
			}
		}

		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();

		/* 다크모드 토글 */
		var toggle = document.getElementById('yw-theme-toggle');
		var root   = document.documentElement;

		toggle.addEventListener('click', function () {
			var isDark = root.getAttribute('data-theme') === 'dark';
			if (isDark) {
				root.removeAttribute('data-theme');
				localStorage.setItem('yw-theme', 'light');
			} else {
				root.setAttribute('data-theme', 'dark');
				localStorage.setItem('yw-theme', 'dark');
			}
		});
	})();
	</script>
	<?php
}
