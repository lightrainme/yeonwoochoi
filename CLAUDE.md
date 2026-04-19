# CLAUDE.md

This file provides guidance to Claude Code when working with code in this repository.

## Project Overview

yeonwoochoi.com — 개인 브랜드 허브 + 콘텐츠 플랫폼.
GeneratePress 부모 테마 + generatepress-child 차일드 테마 구조.
빌드 툴 없음. 순수 PHP / CSS / Vanilla JS.

**수익화 구조**
1. 프로젝트 수주 (웹/앱/디지털 디자인)
2. log368 어필리에이트/리퍼럴 수익
3. 장기: Google AdSense, 디지털 상품

## Development Environment

- Local WordPress: `/Users/yeonwoochoi/Sites/yeonwoochoi/public/`
- Database: `yeonwoo_wp` on `localhost` (credentials in `wp-config.php`)
- WordPress admin: `/wp-admin/`

## Custom Code Location

모든 커스텀 작업은 차일드 테마에서만 진행.

```
public/wp-content/themes/generatepress-child/
├── style.css            # CSS 변수 시스템 + 전체 커스텀 CSS
├── functions.php        # 훅, JS 인젝션, 기타 기능
├── single.php           # 개별 포스트 템플릿
├── front-page.php       # 메인 페이지 템플릿
├── page-log368.php      # Log368 콘텐츠 허브 템플릿
├── archive.php          # 카테고리/태그 아카이브 템플릿
└── inc/
    ├── _load.php        # inc/ 모듈 로드
    ├── assets.php       # CSS/JS enqueue (filemtime 캐시버스팅)
    └── header.php       # GeneratePress 헤더 대체
```

**절대 금지**: `generatepress/` (부모 테마) 직접 수정 금지.

## Site Structure

```
yeonwoochoi.com
├── /origin    — 브랜드 소개
├── /projects  — 프로젝트 쇼룸
└── /log368    — 콘텐츠 허브
    └── /log368/post-slug
```

**카테고리**: AI Tools / Programs / Workflow / Reviews
→ 내부 분류용. URL에 포함하지 않음.

## Design System

### 디자인 방향
- 애플 브랜딩에서 영감 받은 깔끔하고 손으로 만지는 듯한 질감
- 숨 쉬는 여백, 글래스모피즘은 accent로만 제한
- prefers-color-scheme 기반 라이트/다크 모드 자동 전환

### CSS 변수 시스템 (style.css)
모든 디자인 값은 CSS 변수로 관리. 값만 바꿔서 커스터마이징 가능하도록.

```css
:root {
  /* 타이포그래피 */
  --yw-font-base: 'Pretendard', -apple-system, sans-serif;
  --yw-font-serif: /* 미정 */;
  --yw-font-size-body: 1rem;
  --yw-font-size-h1: 2.5rem;
  --yw-font-size-h2: 1.75rem;
  --yw-font-size-h3: 1.375rem;
  --yw-line-height-body: 1.8;
  --yw-letter-spacing-body: -0.01em;

  /* 색상 (라이트 모드) */
  --yw-color-bg: #ffffff;
  --yw-color-surface: #f5f5f7;
  --yw-color-text: #1a1a1a;
  --yw-color-text-muted: #666666;
  --yw-color-accent: #0071e3;
  --yw-color-border: #e5e5e5;

  /* 레이아웃 */
  --yw-container-width: 720px;
  --yw-hero-height: 100vh;
  --yw-header-height: 60px;

  /* 간격 */
  --yw-spacing-section: 4rem;
  --yw-spacing-content: 2rem;

  /* 카테고리별 fallback 그라디언트 (썸네일 없을 때) */
  --yw-gradient-ai-tools: linear-gradient(135deg, #1a1a2e, #16213e);
  --yw-gradient-programs: linear-gradient(135deg, #0f3460, #533483);
  --yw-gradient-workflow: linear-gradient(135deg, #1b4332, #081c15);
  --yw-gradient-reviews: linear-gradient(135deg, #3d0000, #1a0000);
}

@media (prefers-color-scheme: dark) {
  :root {
    --yw-color-bg: #0a0a0a;
    --yw-color-surface: #1a1a1a;
    --yw-color-text: #f0f0f0;
    --yw-color-text-muted: #999999;
    --yw-color-border: #2a2a2a;
  }
}
```

### CSS Naming Conventions
- `yw-` prefix: 사이트 전체 공통 컴포넌트
- `log368-` prefix: Log368 허브 전용 컴포넌트

## Component Specs

### 헤더
- 투명 오버레이로 시작
- 스크롤 시 sticky + 배경 전환
- 네비: Origin / Projects / Log368
- Contact는 푸터 또는 Origin 페이지

### 히어로 — single post
- ref: wormwlrm.github.io 레이아웃 기준
- featured image (썸네일)를 fullscreen 배경으로 사용
- 어두운 오버레이 그라디언트 (텍스트 가독성 확보)
- 썸네일 없을 경우 카테고리별 fallback 그라디언트
- 구성: 카테고리 · 날짜 · 읽는시간 → 제목 → 태그 pills
- 투명 헤더 오버레이

### TOC (목차) — single post
- 본문 h2 / h3 자동 파싱
- 오른쪽 fixed floating 포지션
- 클릭 시 smooth scroll
- scroll spy (현재 위치 하이라이트)
- 구현: PHP heading 파싱 + JS IntersectionObserver

### 포스트 리스트 — page-log368
- ref: 재그재그 블로그 방식
- 상단 풀와이드 히어로 배너
- 카테고리 드롭다운 + 검색창
- 태그 pill 필터 (클라이언트사이드 JS)
- 포스트 행: 카테고리 라벨 → 제목 → 설명 → 날짜

## Architecture

### PHP Module System
`functions.php` → `inc/_load.php` → 각 모듈 파일.
새 기능 추가 시 `inc/` 에 파일 생성 후 `_load.php` 에서 require.

### WordPress Hooks
- `wp_enqueue_scripts` → CSS/JS 로드 (assets.php)
- `generate_header` → 헤더 대체 (header.php)
- `wp_footer` → JS 인젝션

### Cache Busting
`filemtime()` 으로 자동 처리. 수동 버전 변경 불필요.

## Work Phase

**Phase 1 — 디자인 시스템** ← 현재 단계
- CSS 변수 정의
- Pretendard 폰트 enqueue
- 다크/라이트 모드 기반
- 헤더 / 푸터

**Phase 2 — 핵심 페이지**
- single.php (포스트 히어로 + 본문 + TOC)
- front-page.php (메인 히어로)

**Phase 3 — 콘텐츠 허브**
- page-log368.php (필터 + 포스트 리스트)
- archive.php

**Phase 4 — 마무리**
- 반응형 최적화
- Core Web Vitals 점검
- SEO 메타 구조 확인

## Working Rules

파일 수정 전 반드시:
1. 수정할 파일 경로
2. 변경 이유
3. 변경 내용 요약

위 세 가지를 먼저 보고하고, 사용자가 **"진행"** 이라고 하면 그때 코드 작성.

요청이 모호하면 먼저 관련 파일을 읽고 현재 구조를 파악한 뒤 제안.
큰 변경은 전체 파일을 다시 읽고 진행 (부분 편집으로 인한 버전 드리프트 방지).

## WordPress Safety Rules

- GeneratePress 부모 테마 직접 수정 금지
- 플러그인 코어 파일 수정 금지
- 차일드 테마 훅/필터/커스텀 모듈로만 처리
- 부득이한 경우 업데이트 손실 위험 먼저 경고

## Pending Decisions

- [ ] 강조용 세리프 폰트 선정
- [ ] 메인 히어로 카피 문구 확정
- [ ] 카테고리별 fallback 그라디언트 색상 확정
