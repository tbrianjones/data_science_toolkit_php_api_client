<?php

	// a php api client for the data science toolkit
	//
	//	- http://www.datasciencetoolkit.org
	//	- github repo: https://github.com/petewarden/dstk
	//
	//	- launch your own amazon machine image of the data science toolkit:
	//		- doesn't work from the web interface
	//		- prob want to check documentation for the most recent api id
	//		- from the CLI: `ec2-run-instances ami-7b9df412 -t m1.small -z us-east-1b --aws-access-key <ACCESS-KEY> -W <SECRET-KEY> -k <KEY-FILE> -v`
	//
	
	// API Client Usage:
	//
	//	- include this class
	//	- instantiate class
	//	- set the base url
	//		- not specifying a url will default to the open api address
	//	- see developer docs for rest endpoints
	//		- http://www.datasciencetoolkit.org/developerdocs
	//
	
	class Dst_api_client
	{
		
		private $base_url;
		
		public function set_base_url(
			$base_url = 'http://www.datasciencetoolkit.org/'
		) {
			$this->base_url = $base_url;
		}
		
		public function street2coordinates(
			$address // url encoded or not, doesn't matter
		) {
		
		  // clean up address for DST ( dst struggles with slashes, new lines, and po boxes )
			$address = str_replace( "\n", ' ', $address );
			$address = str_replace( "/", '', $address );
			$dst_address = preg_replace( '/((po|p\.o\.)\sbox\s[0-9]{1,8})/i', ' ', $address ); // remove po boxes
			$dst_address = urlencode( $dst_address );
			$response_json = $this->do_request( 'street2coordinates/' . $dst_address );
			
			// check for a street ( DST struggles with some streets and with po boxes )
      $response_array = json_decode( $response_json, TRUE );
      foreach( $response_array as $data ) {
        if( is_null( $data['street_address'] ) OR $data['street_address'] == '' ) {
          $data['street_address'] = $this->extract_street( $address );
          $array[$address] = $data;
          $response_json = json_encode( $array );
        }
        break(1);
      }
      
      return $response_json;
  			
		}
		
		// the streets2coordinates endpoint does some error correcting for po boxes and missing streets
		//  these changes have not been adapted to this endpoint, but should be
		public function google_style_geocoder(
			$address // url encoded or not, doesn't matter
		) {
			return $this->do_request( 'maps/api/geocode/json?sensor=false &address=' . urlencode( str_replace( "\n", ' ', $address ) ) );
		}
		
		public function coordinates2politics(
			$lat,
			$lon
		) {
			return $this->do_request( 'coordinates2politics/' . $lat . ',' . $lon );
		}
				
		// make a curl request to the elasticsearch cluster
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
				CURLOPT_CONNECTTIMEOUT	=> 5,						// timeout on connect ( seconds )
				CURLOPT_TIMEOUT			=> 10						// timeout on response ( seconds )
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
		
		// this will extract the street from a raw address
		//
		//  - this returns the street component ( eg. 4585 Del Monte Ave., or PO Box 792 )
		//
		//  - this should only be used when the DST does not return a street
		//    - the dst fails on some streets
		//    - it also fails on po boxes
		//    - it also seems to fail on raw addresses that have both a po box and a street
		//  
		//  - this solution is not 100%, but it's very good
		//
		private function extract_street( $raw_address ) {
  		
  		// regexes for po box extraction
      $po_box_regex = '/((po|p\.o\.)\sbox\s[0-9]{1,8})/i';
  		
  		// street types to look for and clean up ( this very is good, but not exhaustive )
      $street_types = array();
      $street_types[] = array( "st|street", "St" );
      $street_types[] = array( "dr|drive", "Dr" );
  		$street_types[] = array( "ct|court", "Ct" );
  		$street_types[] = array( "rd|road", "Rd" );
      $street_types[] = array( "ln|lane", "Ln" );
      $street_types[] = array( "ave|avenue", "Ave" );
      $street_types[] = array( "blvd|boulevard", "Blvd" );
      $street_types[] = array( "cir|circle", "Cir" );
      $street_types[] = array( "dr|place", "Pl" );
      $street_types[] = array( "way", "Way" );
      $street_types[] = array( "pkwy|parkway", "Pkwy" );
      $street_types[] = array( "ter|terrace", "Ter" );

      // build street extraction regex
      $street_regex = '/(\d{1,5}\s[^\d].{5,20}\s(';
      foreach( $street_types as $street_type )
        $street_regex .= $street_type[0].'|';
      $street_regex = trim( $street_regex, '|' );
      $street_regex .= ')(\.|\s|\,))/i';

      // look for a street first ( we'd rather have a street address than a po box )
      if( preg_match( $street_regex, $raw_address, $match ) ) {
        // cleanse to match DST format
        $st = $match[0];
        foreach( $street_types as $street_type ) {
          $find = '/((\s)('.$street_type[0].')(\.|\,|\s))/i';
          $replace = '$2'.$street_type[1].'$4';
          if( preg_replace( $find, $replace, $st ) != $st ) {
            $st = preg_replace( $find, $replace, $st );
            break;
          }
        }        
        return trim( $st );
      }
      
      // then look for a po box
      else if( preg_match( $po_box_regex, $raw_address, $match ) ) {
        // cleanse to PO BOX 123 format
        $po = $match[0];
        $find = '/((po|p\.o\.)\sbox\s)/i';
        $replace = 'PO BOX ';
        return trim( preg_replace( $find, $replace, $po ) );
      }
      
      // nothing was found, return false
      else
        return '';
        
		}
		
	} // end class

?>
