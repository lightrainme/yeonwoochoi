<?php
defined( 'ABSPATH' ) || exit;

/* =========================================
   포스트 이모지 메타박스
   -----------------------------------------
   포스트 편집 화면 우측 사이드바에 이모지 입력 칸을 추가.
   저장된 값은 get_post_meta( $id, 'yw_post_emoji', true ) 로 사용.
   ========================================= */

/* 메타박스 등록 */
add_action( 'add_meta_boxes', 'yw_register_emoji_metabox' );

function yw_register_emoji_metabox() {
	add_meta_box(
		'yw_post_emoji',          // 메타박스 ID
		'포스트 이모지',            // 타이틀
		'yw_render_emoji_metabox', // 렌더 콜백
		'post',                   // 포스트 타입
		'side',                   // 위치: 우측 사이드바
		'high'                    // 우선순위: 상단
	);
}

/* 메타박스 HTML 렌더링 */
function yw_render_emoji_metabox( $post ) {
	/* nonce 필드 — 저장 시 요청 출처 검증용 */
	wp_nonce_field( 'yw_emoji_save', 'yw_emoji_nonce' );

	$value = get_post_meta( $post->ID, 'yw_post_emoji', true );
	?>
	<p style="margin: 8px 0 4px;">
		<label for="yw_post_emoji" style="font-size: 12px; color: #757575;">
			포스트를 대표하는 이모지를 입력하세요.
		</label>
	</p>
	<input
		type="text"
		id="yw_post_emoji"
		name="yw_post_emoji"
		value="<?php echo esc_attr( $value ); ?>"
		placeholder="예: 🚀"
		style="width: 100%; font-size: 20px; text-align: center; padding: 6px;"
	/>
	<?php
}

/* 메타값 저장 */
add_action( 'save_post', 'yw_save_emoji_metabox' );

function yw_save_emoji_metabox( $post_id ) {
	/* 자동 저장(autosave) 시 건너뜀 */
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	/* nonce 검증 — 없거나 불일치 시 중단 */
	if ( ! isset( $_POST['yw_emoji_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['yw_emoji_nonce'], 'yw_emoji_save' ) ) return;

	/* 편집 권한 확인 */
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	/* 값 정제 후 저장 (비어 있으면 메타 삭제) */
	if ( isset( $_POST['yw_post_emoji'] ) && $_POST['yw_post_emoji'] !== '' ) {
		update_post_meta( $post_id, 'yw_post_emoji', sanitize_text_field( $_POST['yw_post_emoji'] ) );
	} else {
		delete_post_meta( $post_id, 'yw_post_emoji' );
	}
}
