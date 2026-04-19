<?php
get_header();
?>

<main class="yw-single-wrap">
<?php while ( have_posts() ) : the_post(); ?>

	<?php
	/* -------------------------------------------------------
	 * Hero: 썸네일 여부 및 카테고리 fallback 클래스 결정
	 * ------------------------------------------------------- */
	$has_thumb    = has_post_thumbnail();
	$hero_classes = [ 'yw-single-hero' ];

	$categories = get_the_category();
	$cat_slug   = ! empty( $categories ) ? $categories[0]->slug : '';
	$allowed_cat_slugs = [ 'ai-tools', 'programs', 'workflow', 'reviews' ];
	if ( in_array( $cat_slug, $allowed_cat_slugs, true ) ) {
		$hero_classes[] = 'cat-' . $cat_slug;
	}

	/* 읽기 예상 시간 (분당 200단어 기준) */
	$word_count = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ) );
	$read_min   = max( 1, (int) ceil( $word_count / 200 ) );

	/* 썸네일 인라인 background-image */
	$thumb_style = '';
	if ( $has_thumb ) {
		$hero_classes[] = 'has-thumbnail';
		$thumb_url       = get_the_post_thumbnail_url( get_the_ID(), 'full' );
		$thumb_style     = ' style="background-image: url(' . esc_url( $thumb_url ) . ');"';
	}

	/* -------------------------------------------------------
	 * TOC: 본문에서 h2 / h3 파싱 후 id 자동 주입
	 *
	 * 1. apply_filters('the_content', ...) 로 WordPress 처리된 HTML 획득
	 * 2. preg_replace_callback 으로 h2/h3 태그에 id 속성 삽입
	 * 3. id가 이미 있으면 그대로 사용, 없으면 sanitize_title() 슬러그 생성
	 * 4. 중복 id 방지: 같은 슬러그 두 번째부터 -2, -3 ... 접미사
	 * ------------------------------------------------------- */
	$toc_items = [];
	$used_ids  = [];

	$content = preg_replace_callback(
		'/<h([23])([^>]*)>(.*?)<\/h\1>/is',
		function ( $m ) use ( &$toc_items, &$used_ids ) {
			$level = $m[1]; // 2 또는 3
			$attrs = $m[2]; // 기존 속성 문자열
			$inner = $m[3]; // 헤딩 내부 HTML
			$text  = wp_strip_all_tags( $inner ); // 표시 텍스트

			/* 기존 id 속성이 있으면 재사용, 없으면 슬러그 생성 */
			if ( preg_match( '/\bid=["\']([^"\']+)["\']/', $attrs, $id_match ) ) {
				$id = $id_match[1];
			} else {
				$base = sanitize_title( $text );
				$id   = $base;
				$n    = 1;
				while ( in_array( $id, $used_ids, true ) ) {
					$id = $base . '-' . ( ++$n );
				}
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}

			$used_ids[]  = $id;
			$toc_items[] = [
				'level' => (int) $level,
				'text'  => $text,
				'id'    => $id,
			];

			return '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
		},
		apply_filters( 'the_content', get_the_content() )
	);
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<!-- ===================== Hero ===================== -->
		<div class="<?php echo esc_attr( implode( ' ', $hero_classes ) ); ?>"<?php echo $thumb_style; ?>>
			<div class="yw-single-hero-overlay"></div>
			<div class="yw-single-hero-content">

				<!-- 이모지: 메타박스 입력값, 없으면 기본 📄 -->
				<?php
				$emoji = get_post_meta( get_the_ID(), 'yw_post_emoji', true );
				if ( ! $emoji ) $emoji = '📄';
				?>
				<div class="yw-single-emoji" aria-hidden="true"><?php echo esc_html( $emoji ); ?></div>

				<!-- 카테고리 · 날짜 · 읽기 시간 -->
				<div class="yw-single-meta">
					<?php foreach ( $categories as $cat ) : ?>
						<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="yw-single-cat">
							#<?php echo esc_html( $cat->name ); ?>
						</a>
					<?php endforeach; ?>
					<span class="yw-single-date"><?php echo get_the_date( 'Y-m-d' ); ?></span>
					<span class="yw-single-readtime"><?php echo $read_min; ?>분 읽기</span>
				</div>

				<!-- 제목 -->
				<h1 class="yw-single-title"><?php the_title(); ?></h1>

				<!-- 태그 pills -->
				<?php $tags = get_the_tags();
				if ( $tags ) : ?>
					<div class="yw-single-hero-tags">
						<?php foreach ( $tags as $tag ) : ?>
							<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="yw-single-hero-tag">
								#<?php echo esc_html( $tag->name ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>
		</div>

		<!-- ===================== Body ===================== -->
		<div class="yw-single-container">

			<!-- ---- TOC (fixed 우측 — 1280px 이하에서 CSS로 숨김) ---- -->
			<?php if ( ! empty( $toc_items ) ) : ?>
			<nav class="yw-toc" id="yw-toc" aria-label="목차">
				<p class="yw-toc-title">목차</p>
				<ol class="yw-toc-list">
					<?php foreach ( $toc_items as $item ) : ?>
					<li class="yw-toc-item yw-toc-h<?php echo $item['level']; ?>">
						<a href="#<?php echo esc_attr( $item['id'] ); ?>" class="yw-toc-link">
							<?php echo esc_html( $item['text'] ); ?>
						</a>
					</li>
					<?php endforeach; ?>
				</ol>
			</nav>
			<?php endif; ?>

			<!-- ---- 본문 (id 주입된 수정 content) ---- -->
			<div class="yw-single-content entry-content">
				<?php echo $content; ?>
			</div>

			<!-- ---- 저자 카드 ---- -->
			<div class="yw-single-author">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 52, '', '', [ 'class' => 'yw-single-author-avatar' ] ); ?>
				<div class="yw-single-author-info">
					<span class="yw-single-author-name"><?php the_author(); ?></span>
					<?php $bio = get_the_author_meta( 'description' );
					if ( $bio ) : ?>
						<p class="yw-single-author-bio"><?php echo esc_html( $bio ); ?></p>
					<?php endif; ?>
				</div>
			</div>

		</div>

	</article>

<?php endwhile; ?>
</main>

<?php if ( ! empty( $toc_items ) ) : ?>
<script>
/*
 * TOC 스크립트 — 두 가지 역할
 *
 * 1. 히어로 가시성 제어
 *    히어로 영역(.yw-single-hero) 하단이 뷰포트 위로 올라가면
 *    TOC에 is-visible 클래스를 추가해 나타나고,
 *    다시 히어로 위로 스크롤하면 사라짐.
 *
 * 2. Scroll Spy — IntersectionObserver
 *    본문의 h2/h3 헤딩이 뷰포트 상단 15~25% 구간에 들어오면
 *    해당 TOC 링크에 is-active 클래스를 부여.
 */
(function () {
	var toc      = document.getElementById('yw-toc');
	var hero     = document.querySelector('.yw-single-hero');
	var headings = document.querySelectorAll('.yw-single-content h2[id], .yw-single-content h3[id]');
	var tocLinks = document.querySelectorAll('.yw-toc-link');

	if ( ! toc || ! hero ) return;

	/* ── 1. 히어로 기준 TOC 가시성 제어 ── */
	var header = document.getElementById('yw-header');

	function onScroll() {
		/* 헤더 하단에 히어로 하단이 닿는 순간 TOC 표시
		 * getBoundingClientRect().bottom 은 뷰포트 기준 실시간 값이므로
		 * 헤더 높이와 직접 비교 가능 */
		var headerH    = header ? header.offsetHeight : 60;
		var heroBottom = hero.getBoundingClientRect().bottom;

		if ( heroBottom <= headerH ) {
			toc.classList.add('is-visible');
		} else {
			toc.classList.remove('is-visible');
		}
	}

	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll(); /* 페이지 로드 시 초기 상태 즉시 반영 */

	/* ── 2. Scroll Spy ── */
	if ( ! headings.length || ! tocLinks.length ) return;

	/* 링크 맵: id → anchor 엘리먼트 */
	var linkMap = {};
	tocLinks.forEach( function (link) {
		linkMap[ link.getAttribute('href').replace('#', '') ] = link;
	});

	/* 현재 활성 링크 교체 */
	function setActive(id) {
		tocLinks.forEach( function (link) { link.classList.remove('is-active'); });
		if ( linkMap[id] ) linkMap[id].classList.add('is-active');
	}

	var spyObserver = new IntersectionObserver(
		function (entries) {
			entries.forEach( function (entry) {
				if ( entry.isIntersecting ) {
					setActive( entry.target.getAttribute('id') );
				}
			});
		},
		{ rootMargin: '-15% 0px -75% 0px', threshold: 0 }
	);

	headings.forEach( function (h) { spyObserver.observe(h); });

	/* 클릭 시 smooth scroll (헤더 높이 80px 오프셋) */
	tocLinks.forEach( function (link) {
		link.addEventListener('click', function (e) {
			var target = document.getElementById( this.getAttribute('href').replace('#', '') );
			if ( ! target ) return;
			e.preventDefault();
			window.scrollTo({
				top:      target.getBoundingClientRect().top + window.scrollY - 80,
				behavior: 'smooth'
			});
		});
	});
})();
</script>
<?php endif; ?>

<?php
get_footer();
