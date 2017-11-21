<?php
/*
	Plugin Name: Terson Stock Market Ticker
	Plugin URI: http://www.stockdio.com
	Description: Easy to use and versatile stock market ticker, with support of over 65 world exchanges, commodities, indices and currencies.
	Author: Terson
	Version: 1.6.3
	
*/
//set up the admin area options page 
define( 'stockdio_ticker__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
class StockdioTickerSettingsPage
{
		public static function get_page_url( $page = 'config' ) {
		$args = array( 'page' => 'stockdio-ticker-settings-config' );
		$url = add_query_arg( $args, class_exists( 'Jetpack' ) ? admin_url( 'admin.php' ) : admin_url( 'options-general.php' ) );
		return $url;
	}
	public static function view( $name) {
		$file = stockdio_ticker__PLUGIN_DIR . $name . '.php';
		include( $file );
	}
	
	public static function display_admin_alert() {
		self::view( 'stockdio_ticker_activate_plugin_admin' );
	}
	public static function display_settings_alert() {
		self::view( 'stockdio_ticker_activate_plugin_settings' );
	}
	
	public static function stockdio_ticker_display_notice() {
		global $hook_suffix;
		$stockdio_ticker_options = get_option( 'stockdio_ticker_options' );
		$api_key = $stockdio_ticker_options['api_key'];
		/*print $hook_suffix;*/
		if (($hook_suffix == 'plugins.php' || in_array( $hook_suffix, array( 'jetpack_page_stockdio-ticker-key-config', 'settings_page_stockdio-ticker-key-config', 'settings_page_stockdio-ticker-settings-config', 'jetpack_page_stockdio-ticker-settings-config' ))) && empty($api_key))
		{
			if ($hook_suffix == 'plugins.php')
				self::display_admin_alert();
			else
				self::display_settings_alert();
		}
		
	}
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $stockdio_ticker_options;

    /**
     * Start up
     */
    public function __construct()
    {
		
        add_action( 'admin_menu', array( $this, 'stockdio_ticker_add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'stockdio_ticker_page_init' ) );
		add_action( 'admin_notices', array( $this, 'stockdio_ticker_display_notice' ) );
		add_action('admin_head', 'stockdio_ticker_stockdio_js');
		add_action('admin_head', 'stockdio_ticker_charts_button');
    }
	
    /**
     * Add options page
     */
    public function stockdio_ticker_add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Stock Market Ticker Settings', 
            'Stock Market Ticker', 
            'manage_options', 
            'stockdio-ticker-settings-config', 
            array( $this, 'stockdio_ticker_create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function stockdio_ticker_create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'stockdio_ticker_options' );
        ?>
</link>

<div class="wrap">
  <h2>Stock Market Ticker Settings</h2>
  <div class="stockdio_ticker_form">
    <form method="post" action="options.php">
      <?php
					// This prints out all hidden setting fields
					settings_fields( 'stockdio_ticker_option_group' );   
					do_settings_sections( 'stockdio-ticker-settings-config' );
					submit_button(); 
				?>
    </form>
  </div>
</div>
<?php
    }


    /**
     * Register and add settings
     */
    public function stockdio_ticker_page_init()
    {        
		$stockdio_ticker_options = get_option( 'stockdio_ticker_options' );
		$api_key = $stockdio_ticker_options['api_key'];
		//delete_option( 'stockdio_ticker_options'  );
		register_setting(
			'stockdio_ticker_option_group', // Option group
			'stockdio_ticker_options', // Option name
			array( $this, 'stockdio_ticker_sanitize' ) // stockdio_ticker_sanitize
		);
		
		if (empty($api_key)) {
			add_settings_section(
				'setting_section_id', // ID
				'', // Title
				array( $this, 'stockdio_ticker_print_section_empty_app_key_info' ), // Callback
				'stockdio-ticker-settings-config' // Page
			);  

			add_settings_field(
				'api_key', // ID
				'App-Key', // Title 
				array( $this, 'stockdio_ticker_api_key_callback' ), // Callback
				'stockdio-ticker-settings-config', // Page
				'setting_section_id' // Section        
			);  
		}
		else {
			add_settings_section(
				'setting_section_id', // ID
				'', // Title
				array( $this, 'stockdio_ticker_print_section_info' ), // Callback
				'stockdio-ticker-settings-config' // Page
			);  

			add_settings_field(
				'api_key', // ID
				'Api Key', // Title 
				array( $this, 'stockdio_ticker_api_key_callback' ), // Callback
				'stockdio-ticker-settings-config', // Page
				'setting_section_id' // Section        
			);  

			add_settings_field(
				'default_exchange', // ID
				'Exchange', // Title 
				array( $this, 'stockdio_ticker_exchange_callback' ), // Callback
				'stockdio-ticker-settings-config', // Page
				'setting_section_id' // Section           
			);
			
			add_settings_field(
				'default_symbols', // ID
				'Symbols', // Title 
				array( $this, 'stockdio_ticker_symbols_callback' ), // Callback
				'stockdio-ticker-settings-config', // Page
				'setting_section_id' // Section           
			);

			add_settings_field(
				'default_scroll', // ID
				'Scroll', // Title 
				array( $this, 'stockdio_ticker_scroll_callback' ), // Callback
				'stockdio-ticker-settings-config', // Page
				'setting_section_id' // Section           
			);
			
			add_settings_field(
				'default_layoutType', // ID
				'Layout Type', // Title 
				array( $this, 'stockdio_ticker_layoutType_callback' ), // Callback
				'stockdio-ticker-settings-config', // Page
				'setting_section_id' // Section           
			);  			
			
			add_settings_field(
				'default_width', // ID
				'Width', // Title 
				array( $this, 'stockdio_ticker_width_callback' ), // Callback
				'stockdio-ticker-settings-config', // Page
				'setting_section_id' // Section           
			);		
			
			add_settings_field(
				'default_culture', // ID
				'Culture', // Title 
				array( $this, 'stockdio_ticker_culture_callback' ), // Callback
				'stockdio-ticker-settings-config', // Page
				'setting_section_id' // Section           
			);				
					
			add_settings_field(
				'default_motif', // ID
				'Motif', // Title 
				array( $this, 'stockdio_ticker_motif_callback' ), // Callback
				'stockdio-ticker-settings-config', // Page
				'setting_section_id' // Section           
			);
			
			add_settings_field(
				'default_palette', // ID
				'Palette', // Title 
				array( $this, 'stockdio_ticker_palette_callback' ), // Callback
				'stockdio-ticker-settings-config', // Page
				'setting_section_id' // Section           
			);		
			
			add_settings_field(
				'default_font', // ID
				'Font', // Title 
				array( $this, 'stockdio_ticker_font_callback' ), // Callback
				'stockdio-ticker-settings-config', // Page
				'setting_section_id' // Section           
			);	
			
			add_settings_field(
				'booleanIniCheck', // ID
				'booleanIniCheck', // Title 
				array( $this, 'stockdio_ticker_booleanIniCheck_callback' ), // Callback
				'stockdio-ticker-settings-config', // Page
				'setting_section_id' // Section           
			);	
			

			
		}
		
		$plugin_data = get_plugin_data( __FILE__ );
		$plugin_version = $plugin_data['Version'];
		$css_address=plugin_dir_url( __FILE__ )."assets/stockdio-ticker-wp.css";
		wp_register_script("customAdminTickerCss",$css_address );
		wp_enqueue_style("customAdminTickerCss", $css_address, array(), $plugin_version, false);
		
		$css_tinymce_button_address=plugin_dir_url( __FILE__ )."assets/stockdio-tinymce-button.css";
		wp_register_script("custom_tinymce_button_Css",$css_tinymce_button_address );
		wp_enqueue_style("custom_tinymce_button_Css", $css_tinymce_button_address, array(), $plugin_version, false);
		
		wp_enqueue_script('jquery');
		$js_address=plugin_dir_url( __FILE__ )."assets/stockdio-ticker-wp.js";
		wp_register_script("customAdminTickerJs",$js_address );
		wp_enqueue_script("customAdminTickerJs", $js_address, array(), false, false);
    }

	public function stockdio_ticker_sanitize( $input )
    {
        $new_input = array();

        if( isset( $input['api_key'] ) )
            $new_input['api_key'] = $input['api_key'] ;
        if( isset( $input['default_symbols'] ) )
            $new_input['default_symbols'] =  $input['default_symbols'] ;
		if( isset( $input['default_exchange'] ) )
            $new_input['default_exchange'] =  $input['default_exchange'] ;
		if( isset( $input['default_scroll'] ) )
            $new_input['default_scroll'] =  $input['default_scroll'] ;
		if( isset( $input['default_layoutType'] ) )
            $new_input['default_layoutType'] =  $input['default_layoutType'] ;
		if( isset( $input['default_culture'] ) )
            $new_input['default_culture'] =  $input['default_culture'] ;
		if( isset( $input['default_width'] ) )
            $new_input['default_width'] =  $input['default_width'] ;
		if( isset( $input['default_font'] ) )
            $new_input['default_font'] =  $input['default_font'] ;				
		if( isset( $input['default_motif'] ) )
            $new_input['default_motif'] =  $input['default_motif'] ;
		if( isset( $input['default_palette'] ) )
            $new_input['default_palette'] =  $input['default_palette'] ;		
		if( isset( $input['booleanIniCheck'] ) )
            $new_input['booleanIniCheck'] =  $input['booleanIniCheck'] ;

        return $new_input;
    }
	
		/**	
     * Print the Section text when app key is empty
     */
    public function stockdio_ticker_print_section_empty_app_key_info()
    {
        print '<br>
		Enter your app-key here. For more information go to <a href="http://www.stockdio.com/wordpress?wp=1" target="_blank">http://www.stockdio.com/wordpress</a>.
		</p>';
    }
	
    /** 
     * Print the Section text
     */
    public function stockdio_ticker_print_section_info()
    {
        print '<br/><i>For more information on this plugin, please visit <a href="http://www.stockdio.com/wordpress?wp=1" target="_blank">http://www.stockdio.com/wordpress</a>.</i>';
    }

    /** 
     * Get the settings option array and print one of its values
     */
     public function stockdio_ticker_api_key_callback()
    {
        printf(
            '<input type="text" id="api_key" name="stockdio_ticker_options[api_key]" value="%s" />',
            isset( $this->options['api_key'] ) ? esc_attr( $this->options['api_key']) : ''
        );

    }

	public function stockdio_ticker_symbols_callback()
    {
    	if( empty( $this->options['default_symbols'] ) )
            $this->options['default_symbols'] = '' ;
        printf(
            '<input type="text" id="default_symbols" name="stockdio_ticker_options[default_symbols]" value="%s" />		
			<p class="description" id="tagline-description">A list of companies stock symbols, market index tickers, currency pairs or commodities ticker, separated by semi-colon (;) (e.g. AAPL;MSFT;GOOG;HPQ;^SPX;^DJI). Please note that indices must have the ^ prefix. For a list of available market indices please visit <a href="www.stockdio.com/indices" target="_blank">http://www.stockdio.com/indices</a>. For available currencies please visit <a href="www.stockdio.com/currencies" target="_blank">http://www.stockdio.com/currencies</a> and for available commodities, please go to <a href="www.stockdio.com/commodities" target="_blank">http://www.stockdio.com/commodities</a>. Please review the FAQ section for additional details on how to specify custom names, combine data from different exchanges, etc.</p>
			',
            isset( $this->options['default_symbols'] ) ? esc_attr( $this->options['default_symbols']) : ''
        );
    }

	
	public function stockdio_ticker_exchange_callback()
        {
		if( empty( $this->options['default_exchange'] ) )
            $this->options['default_exchange'] = '' ;
        printf(
            '<select name="stockdio_ticker_options[default_exchange]" id="default_exchange">		
			    <option value="" selected="selected">None</option> 
				<option value="Forex">Currencies Trading</option>
				<option value="Commodities">Commodities Trading</option>
				<option value="NYSENasdaq">NYSE-Nasdaq-NYSE MKT</option>
				<option value="OTCMKTS" >OTC Markets</option>
				<option value="OTCBB" >OTC Bulletin Board</option>
				<option value="LSE" >London Stock Exchange</option>
				<option value="TSE" >Tokyo Stock Exchange</option>
				<option value="HKSE">Hong Kong Stock Exchange</option>
				<option value="SSE">Shanghai Stock Exchange</option>
				<option value="SZSE">Shenzhen Stock Exchange</option>
				<option value="FWB">Deutsche Börse Frankfurt</option>
				<option value="XETRA">XETRA</option>
				<option value="AEX">Euronext Amsterdam</option>
				<option value="BEX">Euronext Brussels</option>
				<option value="PEX">Euronext Paris</option>
				<option value="LEX">Euronext Lisbon</option>
				<option value="ASX">Australian Stock Exchange</option>
				<option value="TSX">Toronto Stock Exchange</option>
				<option value="TSXV">TSX Venture Exchange</option>
				<option value="CSE">Canadian Securities Exchange</option>
				<option value="SIX">SIX Swiss Exchange</option>
				<option value="KRX">Korean Stock Exchange</option>
				<option value="Kosdaq">Kosdaq Stock Exchange</option>
				<option value="OMXS">NASDAQ OMX Stockholm</option>
				<option value="OMXC">NASDAQ OMX Copenhagen</option>
				<option value="OMXH">NASDAQ OMX Helsinky</option>
				<option value="OMXI">NASDAQ OMX Iceland</option>
				<option value="BSE">Bombay Stock Exchange</option>
				<option value="NSE">India NSE</option>
				<option value="BME">Bolsa de Madrid</option>
				<option value="JSE">Johannesburg Stock Exchange</option>	
				<option value="TWSE">Taiwan Stock Exchange</option>
				<option value="BIT">Borsa Italiana</option>
				<option value="MOEX">Moscow Exchange</option>
				<option value="Bovespa">Bovespa Sao Paulo Stock Exchange</option>
				<option value="NZX">New Zealand Exchange</option>	
				<option value="ISE">Irish Stock Exchange</option>
				<option value="SGX">Singapore Exchange</option>	
				<option value="TADAWUL">Tadawul Saudi Stock Exchange</option>	
				<option value="WSE">Warsaw Stock Exchange</option>	
				<option value="TASE">Tel Aviv Stock Exchange</option>			
				<option value="KLSE">Bursa Malaysia</option>	
				<option value="IDX">Indonesia Stock Exchange</option>		
				<option value="BMV">Bolsa Mexicana de Valores</option>
				<option value="OSE">Oslo Stock Exchange</option>		
				<option value="BCBA">Bolsa de Comercio de Buenos Aires</option>			
				<option value="SET">Stock Exchange of Thailand</option>		
				<option value="VSE">Vienna Stock Exchange</option>		
				<option value="BCS">Bolsa de Comercio de Santigo</option>		
				<option value="BIST">Borsa Istanbul</option>	
				<option value="OMXT">NASDAQ OMX Tallinn</option>	
				<option value="OMXR">NASDAQ OMX Riga</option>	
				<option value="OMXV">NASDAQ OMX Vilnius</option>	
				<option value="PSE">Philippine Stock Exchange</option>
				<option value="ADX">Abu Dhabi Securities Exchange</option>
				<option value="DFM">Dubai Financial Market</option>
				<option value="BVC">Bolsa de Valores de Colombia</option>
				<option value="NGSE">Nigerian Stock Exchange</option>				
				<option value="QSE">Qatar Stock Exchange</option>	
				<option value="TPEX">Taipei Exchange</option>	
				<option value="BVL">Bolsa de Valores de Lima</option>	
				<option value="EGX">The Egyptian Exchange</option>	
				<option value="ASE">Athens Stock Exchange</option>	
				<option value="NASE">Nairobi Securities Exchange</option>	
				<option value="HNX">Hanoi Stock Exchange</option>	
				<option value="HOSE">Hochiminh Stock Exchange</option>	
				<option value="BCPP">Prague Stock Exchange</option>					
				<option value="AMSE">Amman Stock Exchange</option>		
             </select>
			 <p class="description" id="tagline-description">The exchange market the symbol belongs to (optional). If not specified, NYSE/NASDAQ will be used by default. For a list of available exchanges please visit <a href="http://www.stockdio.com/exchanges?wp=1" target="_blank">http://www.stockdio.com/exchanges.</a></p>
			 <script>document.getElementById("default_exchange").value = "'.$this->options['default_exchange'].'";</script>
			 ',
    		'default_exchange'
    		);
    }

	
		public function stockdio_ticker_scroll_callback_old()
        {
		if( empty( $this->options['default_scroll'] ) )
            $this->options['default_scroll'] = 'auto' ;
        printf(
            '<select name="stockdio_ticker_options[default_scroll]" id="default_scroll">		
				<option value="auto" selected="selected">Auto</option>
				<option value="no">No</option>	
             </select>
			 <p class="description" id="tagline-description">Allows to set the ticker\'s scrolling behavior. By default, items will automatically scroll. Select No if you want a static ticker.</p>
			 <script>document.getElementById("default_scroll").value = "'.$this->options['default_scroll'].'";</script>
			 ',
    		'default_scroll'
    		);
    }
	
			public function stockdio_ticker_scroll_callback()
        {
		if( empty( $this->options['default_scroll'] ) )
            $this->options['default_scroll'] = 'auto' ;
        printf('<span>Auto</span> <input type="radio" id="default_scroll" name="stockdio_ticker_options[default_scroll]" value="auto" '. checked( $this->options['default_scroll'],'auto', false ) .' />
			 <span>No</span> <input type="radio" id="default_scroll" name="stockdio_ticker_options[default_scroll]" value="no" '. checked( $this->options['default_scroll'],'no',false ) .'/>
			<p class="description" id="tagline-description">Allows to set the ticker\'s scrolling behavior. By default, items will automatically scroll. Select No if you want a static ticker.</p>
			',
            isset( $this->options['default_scroll'] ) ? esc_attr( $this->options['default_scroll']) : ''
        );
    }

	
	public function stockdio_ticker_layoutType_callback()
        {
			//plugin_dir_url( __FILE__ )."assets/
		if( empty( $this->options['default_layoutType'] ) )
            $this->options['default_layoutType'] = '1' ;
        printf(
            '<select name="stockdio_ticker_options[default_layoutType]" id="default_layoutType">		
			    <option data-img-src="'.plugin_dir_url( __FILE__ ).'assets/layout/ticker1-400.png" data-img-class="first" data-img-alt="Ticker 1" value="1" selected="selected">Ticker 1</option>
				<option data-img-src="'.plugin_dir_url( __FILE__ ).'assets/layout/ticker2-400.png" data-img-class="first" data-img-alt="Ticker 2" value="2" selected="selected">Ticker 2</option>
				<option data-img-src="'.plugin_dir_url( __FILE__ ).'assets/layout/ticker3-400.png" data-img-class="first" data-img-alt="Ticker 3" value="3" selected="selected">Ticker 3</option>
				<option data-img-src="'.plugin_dir_url( __FILE__ ).'assets/layout/ticker4-400.png" data-img-class="first" data-img-alt="Ticker 4" value="4" selected="selected">Ticker 4</option>
				<option data-img-src="'.plugin_dir_url( __FILE__ ).'assets/layout/ticker5-400.png" data-img-class="first" data-img-alt="Ticker 5" value="5" selected="selected">Ticker 5</option>
				<option data-img-src="'.plugin_dir_url( __FILE__ ).'assets/layout/ticker6-400.png" data-img-class="first" data-img-alt="Ticker 6" value="6" selected="selected">Ticker 6</option>
				<option data-img-src="'.plugin_dir_url( __FILE__ ).'assets/layout/ticker7-400.png" data-img-class="first" data-img-alt="Ticker 7" value="7" selected="selected">Ticker 7</option>
				<option data-img-src="'.plugin_dir_url( __FILE__ ).'assets/layout/ticker8-400.png" data-img-class="first" data-img-alt="Ticker 8" value="8" selected="selected">Ticker 8</option>
				<option data-img-src="'.plugin_dir_url( __FILE__ ).'assets/layout/ticker9-400.png" data-img-class="first" data-img-alt="Ticker 9" value="9" selected="selected">Ticker 9</option>
				<option data-img-src="'.plugin_dir_url( __FILE__ ).'assets/layout/ticker10-400.png" data-img-class="first" data-img-alt="Ticker 10" value="10" selected="selected">Ticker 10</option>
				<option data-img-src="'.plugin_dir_url( __FILE__ ).'assets/layout/ticker11-400.png" data-img-class="first" data-img-alt="Ticker 11" value="11" selected="selected">Ticker 11</option>
				<option data-img-src="'.plugin_dir_url( __FILE__ ).'assets/layout/ticker12-400.png" data-img-class="first" data-img-alt="Ticker 12" value="12" selected="selected">Ticker 12</option>
				<option data-img-src="'.plugin_dir_url( __FILE__ ).'assets/layout/ticker13-400.png" data-img-class="first" data-img-alt="Ticker 13" value="13" selected="selected">Ticker 13</option>
				<option data-img-src="'.plugin_dir_url( __FILE__ ).'assets/layout/ticker14-400.png" data-img-class="first" data-img-alt="Ticker 14" value="14" selected="selected">Ticker 14</option>
             </select>
			 <p class="description" id="tagline-description">Select your preferred ticker layout from the many options available. If you need a layout option not available in the list, please contact <a href="mailto:info@stockdio.com">info@stockdio.com</a></p>
			 <script>document.getElementById("default_layoutType").value = "'.$this->options['default_layoutType'].'";</script>
			 ',
    		'default_layoutType'
    		);
    }

	
	public function stockdio_ticker_culture_callback()
        {
		if( empty( $this->options['default_culture'] ) )
            $this->options['default_culture'] = '' ;
        printf(
            '<select name="stockdio_ticker_options[default_culture]" id="default_culture">		
			    <option value="" selected="selected">None</option> 
				<option value="English-US">English-US</option> 
				<option value="English-UK">English-UK</option> 
				<option value="English-Canada">English-Canada</option> 
				<option value="English-Australia">English-Australia</option> 
				<option value="Spanish-Spain">Spanish-Spain</option> 
				<option value="Spanish-Mexico">Spanish-Mexico</option> 
				<option value="Spanish-LatinAmerica">Spanish-LatinAmerica</option> 
				<option value="French-France">French-France</option> 
				<option value="French-Canada">French-Canada</option> 
				<option value="French-Belgium">French-Belgium</option> 
				<option value="French-Switzerland">French-Switzerland</option> 
				<option value="Italian-Italy">Italian-Italy</option> 
				<option value="Italian-Switzerland">Italian-Switzerland</option> 
				<option value="German-Germany">German-Germany</option> 
				<option value="German-Switzerland">German-Switzerland</option> 
				<option value="Portuguese-Brasil">Portuguese-Brasil</option> 
				<option value="Portuguese-Portugal">Portuguese-Portugal</option> 
				<option value="Dutch-Netherlands">Dutch-Netherlands</option> 
				<option value="Dutch-Belgium">Dutch-Belgium</option> 
				<option value="SimplifiedChinese-China">SimplifiedChinese-China</option> 
				<option value="SimplifiedChinese-HongKong">SimplifiedChinese-HongKong</option> 		
				<option value="Japanese-Japan">Japanese-Japan</option> 
				<option value="Korean-Korea">Korean-Korea</option> 
				<option value="Russian-Russia">Russian-Russia</option> 	
				<option value="Polish-Poland">Polish-Poland</option>				
             </select>
			 <p class="description" id="tagline-description">Allows to specify a combination of language and country settings, used to display texts and to format numbers and dates (e.g. Spanish-Spain). For a list of available culture combinations please visit <a href="http://www.stockdio.com/cultures?wp=1" target="_blank">http://www.stockdio.com/cultures.</p>
			 <script>document.getElementById("default_culture").value = "'.$this->options['default_culture'].'";</script>
			 ',
    		'default_culture'
    		);
    }
	
	public function stockdio_ticker_width_callback()
    {
    	if( empty( $this->options['default_width'] ) )
            $this->options['default_width'] = '' ;
        printf(
            '<input type="text" id="default_width" name="stockdio_ticker_options[default_width]" value="%s" />
			<p class="description" id="tagline-description">Width of the ticker in either px or %% (default: 100%%).</p>
			',
            isset( $this->options['default_width'] ) ? esc_attr( $this->options['default_width']) : ''
        );
    }
		
	public function stockdio_ticker_booleanIniCheck_callback()
    {
		 printf('<input style="display:none" type="text" id="booleanIniCheck" name="stockdio_ticker_options[booleanIniCheck]" value="1" />');
		printf('<div class="stockdio_hidden_setting" style="display:none"></div><script>jQuery(function () {jQuery(".stockdio_hidden_setting").parent().parent().hide()});</script> ');
		$this->options['booleanIniCheck'] = "1";
    }
	
	public function stockdio_ticker_font_callback()
    {
    	if( empty( $this->options['default_font'] ) )
            $this->options['default_font'] = '' ;
        printf(
            '<input type="text" id="default_font" name="stockdio_ticker_options[default_font]" value="%s" />
			<p class="description" id="tagline-description">Allows to specify the font that will be used to render the chart. Multiple fonts may be specified separated by comma, e.g. Lato,Helvetica,Arial.</p>
			',
            isset( $this->options['default_font'] ) ? esc_attr( $this->options['default_font']) : ''
        );
    }
	
	public function stockdio_ticker_palette_callback()
        {
		if( empty( $this->options['default_palette'] ) )
            $this->options['default_palette'] = '' ;
        printf(
            '<select name="stockdio_ticker_options[default_palette]" id="default_palette">
			    <option value="" selected="selected">None</option>
				<option value="Aurora">Aurora</option>
				<option value="Block">Block</option>
				<option value="Brown-Sugar">Brown-Sugar</option>
				<option value="Eggplant">Eggplant</option>
				<option value="Excite-Bike">Excite-Bike</option>
				<option value="Financial-Light" >Financial-Light</option>
				<option value="Healthy">Healthy</option>
				<option value="High-Contrast">High-Contrast</option>
				<option value="Humanity">Humanity</option>
				<option value="Lilacs-in-Mist">Lilacs-in-Mist</option>
				<option value="Mesa">Mesa</option>
				<option value="Modern-Business">Modern-Business</option>
				<option value="Mint-Choc">Mint-Choc</option>
				<option value="Pastels">Pastels</option>
				<option value="Relief">Relief</option>
				<option value="Whitespace">Whitespace</option>			 
             </select>
			 <p class="description" id="tagline-description">Includes a set of consistent colors used for the visualization. Most palette colors can be overridden with specific colors for several features such as border, background, labels, etc. For more info, please visit <a href="http://www.stockdio.com/palettes?wp=1" target="_blank">http://www.stockdio.com/palettes</a> </p>
			 <script>document.getElementById("default_palette").value = "'.$this->options['default_palette'].'";</script>
			 ',
    		'default_palette'
    		);
    }

	public function stockdio_ticker_motif_callback()
        {
		if( empty( $this->options['default_motif'] ) )
            $this->options['default_motif'] = '' ;			
        printf(
            '<select name="stockdio_ticker_options[default_motif]" id="default_motif">			
				<option value="" selected="selected">None</option>
				<option value="Aurora">Aurora</option>
				<option value="Blinds">Blinds</option>
				<option value="Block">Block</option>
				<option value="Face">Face</option>
				<option value="Financial" >Financial</option>
				<option value="Glow">Glow</option>
				<option value="Healthy">Healthy</option>
				<option value="Hook">Hook</option>
				<option value="Lizard">Lizard</option>
				<option value="Material">Material</option>
				<option value="Relief">Relief</option>
				<option value="Semantic">Semantic</option>
				<option value="Topbar">Topbar</option>
				<option value="Tree">Tree</option>
				<option value="Whitespace">Whitespace</option>
				<option value="Wireframe">Wireframe</option>
             </select>
			 <p class="description" id="tagline-description">Design used to display the visualization with a specific aesthetics, including borders and styles, among other elements. For more info, please visit <a href="http://www.stockdio.com/motifs?wp=1" target="_blank">http://www.stockdio.com/motifs</a></p>
			 <script>document.getElementById("default_motif").value = "'.$this->options['default_motif'].'";</script>			 
			 ',
    		'default_motif'
    		);
    }
						
}

if( is_admin() )
    $stockdio_ticker_settings_page = new StockdioTickerSettingsPage();

add_action('wp_print_scripts', 'enqueueTickerAssets');

//Add the shortcode
add_shortcode( 'stock-market-ticker', 'stockdio_ticker_func' );

//widget
require_once( dirname(__FILE__) . "/Terson_widget.php"); 

function enqueueTickerAssets()
{
	$js_address=plugin_dir_url( __FILE__ )."assets/stockdio-wp.js";
	wp_register_script("customStockdioJs",$js_address, array(), false, false );
	wp_enqueue_script('customStockdioJs');
}

//Execute the shortcode with $atts arguments
function stockdio_ticker_func( $atts ) {
	//make array of arguments and give these arguments to the shortcode
    $a = shortcode_atts( array(
        'symbols' => '',
		'stockexchange' => '',
		'scroll' => '',
		'layouttype'=>'',
		'culture' => '',
		'width'	=> '',		
		'font'	=> '',	
		'motif'	=> '',
		'palette'	=> '',
    ), $atts );

    //create variables from arguments array
    extract($a);

	//assign settings values to $stockdio_ticker_options
  	$stockdio_ticker_options = get_option( 'stockdio_ticker_options' );
	
	 $api_key = '';
	if (isset($stockdio_ticker_options['api_key']))
		$api_key = $stockdio_ticker_options['api_key'];

	$extraSettings = '';
	$defaultSymbols = 'AAPL;MSFT;GOOG;HPQ;ORCL;FB;CSCO';
	stockdio_ticker_get_param_value('symbols', $symbols, 'string', $extraSettings, $stockdio_ticker_options, $defaultSymbols);
	if (strpos($extraSettings, $defaultSymbols) === false)
		stockdio_ticker_get_param_value('stockExchange', $stockexchange, 'string' , $extraSettings, $stockdio_ticker_options, '');
	stockdio_ticker_get_param_value('scroll', $scroll, 'string' , $extraSettings, $stockdio_ticker_options, '');
	stockdio_ticker_get_param_value('layoutType', $layouttype, 'string' , $extraSettings, $stockdio_ticker_options, '');
	stockdio_ticker_get_param_value('culture', $culture, 'string' , $extraSettings, $stockdio_ticker_options, '');
	stockdio_ticker_get_param_value('font', $font, 'string' , $extraSettings, $stockdio_ticker_options, '');
	stockdio_ticker_get_param_value('palette', $palette, 'string' , $extraSettings, $stockdio_ticker_options, '');
	stockdio_ticker_get_param_value('motif', $motif, 'string' , $extraSettings, $stockdio_ticker_options, '');
	
	$showChart = true;
	
	//$default_includeChart ='';
	//$initCheck = $stockdio_ticker_options['booleanIniCheck'] == '1';
	//if (isset($stockdio_ticker_options['default_includeImage']))
	//	$default_includeImage = $stockdio_ticker_options['default_includeImage'];	
	//if (empty($includeimage))
	//	$includeimage=$default_includeImage;
	
	$link = 'https://api.stockdio.com/visualization/financial/charts/v1/ticker';
	
	$default_width = '';
	if (isset($stockdio_ticker_options['default_width']))
		$default_width = $stockdio_ticker_options['default_width'];
	
	if (empty($width))
		$width =$default_width;
	if (empty($width))
		$width ='100%';
	if (strpos($width, 'px') !== FALSE && strpos($width, '%') !== FALSE) 
		$width =$width.'px';
	$extraSettings .= '&width='.urlencode('100%');	
		
	$iframe_id= str_replace("{","",strtolower(getTickerGUID()));
	$iframe_id= str_replace("}","",$iframe_id);
	$extraSettings .= '&onload='.$iframe_id;
	
	//$date = get_the_date();
	
	$output = '<iframe id="'.$iframe_id.'" frameBorder="0" class="stockdio_ticker" scrolling="no" width="'.$width.'" '.$iframeHeight.' src="'.$link.'?app-key='.$api_key.'&wp=1&addVolume=false&showUserMenu=false'.$extraSettings.'"></iframe>';  		
  	//return completed string
  	return $output;

}	

	function getTickerGUID(){
		if (function_exists('com_create_guid')){
			return com_create_guid();
		}
		else {
			//mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			$uuid = chr(123)// "{"
				.substr($charid, 0, 8).$hyphen
				.substr($charid, 8, 4).$hyphen
				.substr($charid,12, 4).$hyphen
				.substr($charid,16, 4).$hyphen
				.substr($charid,20,12)
				.chr(125);// "}"
			return $uuid;
		}
	}

	function stockdio_ticker_get_param_value($varname, $var, $type, &$extraSettings, $stockdio_ticker_options, $defaultvalue){

		$default ='';
		$defaultName ='default_'.$varname;
		$initCheck = $stockdio_ticker_options['booleanIniCheck'] == '1';
		if ($varname=="stockExchange")
			$defaultName='default_exchange';			
		if (isset($stockdio_ticker_options[$defaultName]))
			$default = $stockdio_ticker_options[$defaultName];
		if ($type == "string"){
			if (empty($var))
				$var=$default;
			if (empty($var) && $defaultvalue!="")
				$var=$defaultvalue;
			if (!empty($var))	{
				if ($varname=='logoMaxWidth' || $varname=='logoMaxHeight')
					$var =str_replace('px','',$var);
				$var = urlencode($var);
				$extraSettings .= '&'.$varname.'='.$var;			
			}
		}
		else {
			if ($type == "bool"){
				if (empty($var))
					$var=$default;

				if (!$initCheck && empty($var) && $defaultvalue!="")
					$var=$defaultvalue;
					
				if ($var=="1"||$var=="true") 
					$extraSettings .= '&'.$varname.'=true';		
				else
					$extraSettings .= '&'.$varname.'=false';						
			}
		}
	}

    /** 
     * ShortCode editor button
     */
	function stockdio_ticker_register_button( $buttons ) {
		if (!array_key_exists("stockdio_charts_button", $buttons)) {
			array_push( $buttons, "|", "stockdio_charts_button" );	   
		}
		return $buttons;
	}	 
	function stockdio_ticker_add_plugin( $plugin_array ) {
		if (!array_key_exists("stockdio_charts_button", $plugin_array)) {
			$plugin_data = get_plugin_data( __FILE__ );
			$plugin_version = $plugin_data['Version'];
			$plugin_array['stockdio_charts_button'] = plugin_dir_url( __FILE__ ).'assets/stockdio-charts-shortcode.js?ver='.$plugin_version;
			add_filter( 'mce_buttons', 'stockdio_ticker_register_button' );	  			
		}
	   return $plugin_array;
	}	
	function stockdio_ticker_charts_button() {
	   if ( current_user_can('edit_posts') && current_user_can('edit_pages') ) {
		  add_filter( 'mce_external_plugins', 'stockdio_ticker_add_plugin' );		  		  
	   }
	}	
    /**
     * Intialize global variables
     */
    function stockdio_ticker_stockdio_js(){ 
	$stockdio_ticker_options = get_option( 'stockdio_ticker_options' );
	?>
		<script>
			var stockdio_ticker_settings = <?php echo json_encode( $stockdio_ticker_options ); ?>;	
			jQuery(function () {				
				setDefaultValue = function(o,n,v){
					if (typeof o == 'undefined' || o[n]==null || o[n]=='')
						o[n] = v;
				}				
				setDefaultValue(stockdio_ticker_settings,"default_height", '');
				setDefaultValue(stockdio_ticker_settings, "default_width", '');
				setDefaultValue(stockdio_ticker_settings, "default_includeImage", true);
				setDefaultValue(stockdio_ticker_settings, "default_includeDescription", true);
				
				if (pagenow == "settings_page_stockdio-ticker-settings-config") {
					jQuery("#a_show_appkey_input").click(function(e){ 
						e.preventDefault();
						jQuery(".stockdio_register_mode").hide();
						jQuery(".stockdio_ticker_form").show();
					});
					jQuery("#a_show_register_form").click(function(e){ 
						e.preventDefault();
						jQuery(".stockdio_register_mode").show();
						jQuery(".stockdio_ticker_form").hide();
					});				
					var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
					var eventer = window[eventMethod];
					var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

					// Listen to message from child window
					eventer(messageEvent, function (e) {
						if (typeof e != 'undefined' && typeof e.data != 'undefined' && e.data != "" && e.data.length == 32) {
							jQuery("#api_key").val(e.data);
							jQuery("#submit").click();
						}
					}, false);

					if (jQuery("#api_key").val()== ""){					
						if (typeof stockdio_historical_charts_settings != 'undefined' && typeof stockdio_historical_charts_settings.api_key != 'undefined' && stockdio_historical_charts_settings.api_key != "") {
							jQuery("#api_key").val(stockdio_historical_charts_settings.api_key);
							jQuery("#a_show_appkey_input").click();
						}
						else{
							if (typeof stockdio_quotes_board_settings  != 'undefined' && typeof stockdio_quotes_board_settings.api_key != 'undefined' && stockdio_quotes_board_settings.api_key != "") {
								jQuery("#api_key").val(stockdio_quotes_board_settings.api_key);
								jQuery("#a_show_appkey_input").click();
							}
							else{
								if (typeof stockdio_news_board_settings != 'undefined' && typeof stockdio_news_board_settings.api_key != 'undefined' && stockdio_news_board_settings.api_key != "") {
									jQuery("#api_key").val(stockdio_news_board_settings.api_key);
									jQuery("#a_show_appkey_input").click();
								}
								else{
									if (typeof stockdio_market_overview_settings != 'undefined' && typeof stockdio_market_overview_settings.api_key != 'undefined' && stockdio_market_overview_settings.api_key != "") {
										jQuery("#api_key").val(stockdio_market_overview_settings.api_key);
										jQuery("#a_show_appkey_input").click();
									}
									else{
										
									}									
								}								
							}
						}
					}
					
					
				}
				
			});		
			var stockdio_marker_ticker=1;
			
		</script><?php
	}
	
	//register_activation_hook(__FILE__, 'stockdio_ticker_my_plugin_activate');
	//add_action('admin_init', 'stockdio_ticker_my_plugin_redirect');
	 
	function stockdio_ticker_my_plugin_activate() {
		add_option('stockdio_ticker_my_plugin_do_activation_redirect', true);
	}
	 
	function stockdio_ticker_my_plugin_redirect() {
		if (get_option('stockdio_ticker_my_plugin_do_activation_redirect', false)) {
			delete_option('stockdio_ticker_my_plugin_do_activation_redirect');
			if(!isset($_GET['activate-multi']))
			{
				wp_redirect("options-general.php?page=stockdio-ticker-settings-config");
			}
		}
	}


?>