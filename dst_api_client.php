<?php

	//
	// A PHP API Client for the Data Science Toolkit
	//
	//	- http://www.datasciencetoolkit.org
	//	- github repo: https://github.com/petewarden/dstk
	//
	
	class Dst_api_client
	{
		
		private $base_url;
		
		// if no url is passed, then the public DST server will be used
		public function set_base_url(
			$base_url = 'http://www.datasciencetoolkit.org/'
		) {
			$this->base_url = $base_url;
		}
		
		public function street2coordinates(
			$address // string containing address
		) {
			return $this->do_request( 'street2coordinates/' . urlencode( $address ) );
		}
		
		public function google_style_geocoder(
			$address // string containing address
		) {
			return $this->do_request( 'maps/api/geocode/json?sensor=false &address=' . urlencode( $address ) );
		}
		
		public function coordinates2politics(
			$lat,
			$lon
		) {
			return $this->do_request( 'coordinates2politics/' . $lat . ',' . $lon );
		}
				
		// make a curl request to the dst server specified by $this->set_base_url()
		//
		private function do_request(
			$uri,						// the uri of the request ( leading slashes are removed )
			$display_headers = FALSE	// set to TRUE to output the headers ... generally for development and testing
		) {
			
			// remove leading slash if one exists in the passed uri
			$uri = ltrim( $uri, '/' );
			
			// initialize curl
			$Curl = curl_init();
			
			// set basic curl configuration
			$options = array(
				CURLOPT_URL				=> $this->base_url . $uri,	// the url of the request
				CURLOPT_CUSTOMREQUEST	=> 'GET',					// the http request method ( GET, POST, PUT, DELETE )
				CURLOPT_RETURNTRANSFER	=> true,					// return web page
				CURLOPT_CONNECTTIMEOUT	=> 2,						// timeout on connect ( seconds )
				CURLOPT_TIMEOUT			=> 2						// timeout on response ( seconds )
			);
			
			// make curl request
			curl_setopt_array( $Curl, $options );
			$response			= '';
			$response			= curl_exec( $Curl );
			$error_number		= curl_errno( $Curl );
			$error_message		= curl_error( $Curl );
			$header				= curl_getinfo( $Curl );
			curl_close( $Curl );
									
			// output cURL errors if there were any
			if( $error_number > 0 ) {
				throw new Exception( "Data Science Toolkit server could not be reached due to a cURL connection error. cURL Error $error_number: $error_message" );
			}
			
			return $response;
						
		}
		
	}

?>
