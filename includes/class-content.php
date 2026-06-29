<?php
/**
 * Content — default measures data and page rendering.
 *
 * @package Nera_Voluntary_Code
 */

defined( 'ABSPATH' ) || exit;

/**
 * Provides default commitment measures and renders the disclosure page.
 */
class Nera_VC_Content {

	/**
	 * Hook into WordPress.
	 */
	public static function init() {}

	/**
	 * Default Player Protection measures.
	 *
	 * @return array
	 */
	public static function default_protections() {
		return array(
			array(
				'title' => 'Age verification (18+)',
				'body'  => 'Entry is restricted to people aged 18 or over. We run age checks at registration and before entry.',
			),
			array(
				'title' => 'Spending limits',
				'body'  => 'We apply proportionate monthly spend limits across all our draws, and you can set your own monthly limit (including £0) when you register or any time from your account.',
			),
			array(
				'title' => 'Pause, suspend or close your account',
				'body'  => 'You can take a break, suspend your account (minimum 6 months) or close it permanently at any time. We send no marketing while your account is suspended.',
			),
			array(
				'title' => 'Credit card limits',
				'body'  => 'Credit-card entries are capped at £250 per player per month, and we never accept credit cards for instant-win draws.',
			),
			array(
				'title' => 'Support when you need it',
				'body'  => 'We signpost free, confidential help (Citizens Advice, National Debtline, Samaritans, Mind) and monitor for signs of harm so we can step in.',
			),
		);
	}

	/**
	 * Default Transparency measures.
	 *
	 * @return array
	 */
	public static function default_transparency() {
		return array(
			array(
				'title' => 'Ticket numbers & odds',
				'body'  => 'Maximum ticket numbers and your odds of winning are shown clearly before you enter.',
			),
			array(
				'title' => 'Closing dates',
				'body'  => 'Every draw\'s closing date is displayed up front and is never changed once set.',
			),
			array(
				'title' => 'Prize details',
				'body'  => 'Full prize details are shown. The advertised prize (or a fair cash alternative) is always awarded — never reduced or cancelled because of low ticket sales.',
			),
			array(
				'title' => 'Free entry route',
				'body'  => 'Every paid draw has a genuine free postal entry route with an equal chance of winning, shown prominently before purchase.',
			),
			array(
				'title' => 'Fair draws',
				'body'  => 'Winners are selected by a verifiably random, auditable process, in accordance with the laws of chance.',
			),
		);
	}

	/**
	 * Default Accountability measures.
	 *
	 * @return array
	 */
	public static function default_accountability() {
		return array(
			array(
				'title' => 'Complaints process',
				'body'  => 'We have a clear complaints process with defined response times.',
			),
			array(
				'title' => 'Independent dispute resolution',
				'body'  => 'If we cannot resolve your complaint to your satisfaction, you can escalate it to an independent alternative dispute resolution (ADR) provider: [ADR Provider].',
			),
			array(
				'title' => 'Who we are & how to reach us',
				'body'  => 'Operator: [Business Name], [Business Address]. Contact us at [Email]. Our prize draws run under the free-entry exemption and are not licensed by the Gambling Commission. You can give feedback on the Code to DCMS at prizedrawcode@dcms.gov.uk.',
			),
			array(
				'title' => 'Ongoing compliance',
				'body'  => 'We monitor and regularly review our compliance with the Code, and we require third parties who support our draws to follow it too.',
			),
		);
	}

	/**
	 * Get measures for a given area, merging ACF overrides with defaults.
	 *
	 * @param string $area Area key: 'protections', 'transparency', or 'accountability'.
	 * @return array
	 */
	public static function get_measures( $area ) {
		$map = array(
			'protections'    => array( 'field' => 'vc_measures_protections',    'default' => 'default_protections' ),
			'transparency'   => array( 'field' => 'vc_measures_transparency',   'default' => 'default_transparency' ),
			'accountability' => array( 'field' => 'vc_measures_accountability', 'default' => 'default_accountability' ),
		);

		if ( ! isset( $map[ $area ] ) ) {
			return array();
		}

		$field_name    = $map[ $area ]['field'];
		$default_method = $map[ $area ]['default'];

		$rows = null;
		if ( function_exists( 'get_field' ) ) {
			$acf_value = get_field( $field_name, Nera_VC_Settings::OPTIONS_POST_ID );
			if ( is_array( $acf_value ) && ! empty( $acf_value ) ) {
				$rows = $acf_value;
			}
		}

		if ( null === $rows ) {
			$rows = self::$default_method();
		}

		$normalized = array();
		foreach ( $rows as $row ) {
			$title = '';
			$body  = '';

			if ( isset( $row['title'] ) ) {
				$title = sanitize_text_field( (string) $row['title'] );
			}
			if ( isset( $row['body'] ) ) {
				$body = (string) $row['body'];
			}

			if ( '' === $title ) {
				continue;
			}

			$normalized[] = array(
				'title' => $title,
				'body'  => $body,
			);
		}

		return $normalized;
	}

	/**
	 * Render the full commitment page HTML.
	 *
	 * @param array $args Optional render arguments.
	 * @return string
	 */
	public static function render_page( $args = array() ) {
		$sections = array(
			array(
				'area'    => 'protections',
				'heading' => esc_html__( 'Player protections', 'nera-voluntary-code-plugin' ),
			),
			array(
				'area'    => 'transparency',
				'heading' => esc_html__( 'Transparency', 'nera-voluntary-code-plugin' ),
			),
			array(
				'area'    => 'accountability',
				'heading' => esc_html__( 'Accountability', 'nera-voluntary-code-plugin' ),
			),
		);

		$intro   = self::replace_tokens( Nera_VC_Settings::intro_copy() );
		$govuk   = Nera_VC_Settings::govuk_url();

		$html  = '<div class="nera-vc-page">' . "\n";

		// Intro block.
		$html .= '<div class="nera-vc-intro">' . wp_kses_post( $intro ) . '</div>' . "\n";

		// GOV.UK link.
		$html .= '<p class="nera-vc-govuk"><a class="nera-vc-govuk__link" href="' . esc_url( $govuk ) . '" target="_blank" rel="noopener noreferrer">'
			. esc_html__( 'Read the full Code on GOV.UK', 'nera-voluntary-code-plugin' )
			. '</a></p>' . "\n";

		// Three sections.
		foreach ( $sections as $section ) {
			$measures = self::get_measures( $section['area'] );

			$html .= '<section class="nera-vc-section">' . "\n";
			$html .= '<h2 class="nera-vc-section__title">' . $section['heading'] . '</h2>' . "\n";
			$html .= '<ul class="nera-vc-measures">' . "\n";

			foreach ( $measures as $measure ) {
				$body_html = self::build_measure_body( $measure['title'], $measure['body'], $section['area'] );

				$html .= '<li class="nera-vc-measure">' . "\n";
				$html .= '<strong class="nera-vc-measure__title">' . esc_html( $measure['title'] ) . '</strong>' . "\n";
				$html .= '<div class="nera-vc-measure__body">' . $body_html . '</div>' . "\n";
				$html .= '</li>' . "\n";
			}

			$html .= '</ul>' . "\n";
			$html .= '</section>' . "\n";
		}

		$html .= '</div>' . "\n";

		return $html;
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Replace [Business Name], [Business Address], [Email], [ADR Provider] tokens.
	 *
	 * @param string $text Input text with tokens.
	 * @return string Text with tokens replaced.
	 */
	private static function replace_tokens( $text ) {
		// [Business Name]
		$business_name = '';
		if ( function_exists( 'get_field' ) ) {
			$business_name = (string) get_field( 'legal_business_name', 'option' );
		}
		if ( '' === $business_name ) {
			$business_name = (string) get_bloginfo( 'name' );
		}
		$business_name = sanitize_text_field( $business_name );

		// [Business Address]
		$business_address = '';
		if ( function_exists( 'get_field' ) ) {
			$business_address = (string) get_field( 'legal_business_address', 'option' );
		}
		if ( '' === $business_address ) {
			$business_address = 'address available on request';
		} else {
			// Collapse newlines to ', '.
			$business_address = preg_replace( '/[\r\n]+/', ', ', $business_address );
			$business_address = sanitize_text_field( $business_address );
		}

		// [Email]
		$email = '';
		if ( function_exists( 'get_field' ) ) {
			$email = (string) get_field( 'legal_contact_email', 'option' );
		}
		if ( '' === $email ) {
			$email = (string) get_option( 'admin_email' );
		}
		$email = sanitize_text_field( $email );

		// [ADR Provider]
		$adr = Nera_VC_Settings::adr_provider();
		if ( '' === $adr ) {
			$adr = 'our nominated ADR provider (details available on request)';
		}
		$adr = sanitize_text_field( $adr );

		$text = str_replace( '[Business Name]',    $business_name,    $text );
		$text = str_replace( '[Business Address]', $business_address, $text );
		$text = str_replace( '[Email]',            $email,            $text );
		$text = str_replace( '[ADR Provider]',     $adr,              $text );

		return $text;
	}

	/**
	 * Build the escaped HTML for a single measure body, applying token replacement
	 * and cross-links.
	 *
	 * @param string $title   The measure title (for cross-link matching).
	 * @param string $raw_body Raw body text (may contain tokens).
	 * @param string $area    Section area ('protections'|'transparency'|'accountability').
	 * @return string Escaped HTML.
	 */
	private static function build_measure_body( $title, $raw_body, $area ) {
		$text = self::replace_tokens( $raw_body );
		$html = wp_kses_post( wpautop( $text ) );

		// Cross-link: Player protections — "Support when you need it".
		if ( 'protections' === $area && 'Support when you need it' === $title ) {
			$html .= self::help_page_link();
		}

		// Cross-link: Accountability — "Complaints process".
		if ( 'accountability' === $area && 'Complaints process' === $title ) {
			$html .= self::complaints_page_link();
		}

		return $html;
	}

	/**
	 * Build the help-page cross-link HTML, or empty string if not resolvable.
	 *
	 * @return string
	 */
	private static function help_page_link() {
		if ( ! function_exists( 'get_option' ) ) {
			return '';
		}

		$page_id = (int) get_option( 'nera_rp_help_page_id' );
		if ( $page_id <= 0 ) {
			return '';
		}

		$page = get_post( $page_id );
		if ( ! $page || 'publish' !== get_post_status( $page ) ) {
			return '';
		}

		$permalink = get_permalink( $page_id );
		if ( ! $permalink ) {
			return '';
		}

		return ' <a class="nera-vc-measure__link" href="' . esc_url( $permalink ) . '">'
			. esc_html__( 'See our Help &amp; support page', 'nera-voluntary-code-plugin' )
			. '</a>';
	}

	/**
	 * Build the complaints-page cross-link HTML, or empty string if not resolvable.
	 *
	 * @return string
	 */
	private static function complaints_page_link() {
		$page = get_page_by_path( 'complaints' );
		if ( ! $page || 'publish' !== get_post_status( $page ) ) {
			$page = get_page_by_path( 'complaints-procedure' );
		}

		if ( ! $page || 'publish' !== get_post_status( $page ) ) {
			return '';
		}

		$permalink = get_permalink( $page->ID );
		if ( ! $permalink ) {
			return '';
		}

		return ' <a class="nera-vc-measure__link" href="' . esc_url( $permalink ) . '">'
			. esc_html__( 'Read our complaints procedure', 'nera-voluntary-code-plugin' )
			. '</a>';
	}
}
