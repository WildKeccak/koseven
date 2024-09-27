<?php
/**
 * UTF8::str_ireplace
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _str_ireplace($search, $replace, $str, & $count = NULL)
{
	if (UTF8::is_ascii($search) AND UTF8::is_ascii($replace) AND UTF8::is_ascii($str))
		return str_ireplace($search, $replace, $str, $count);

	if (is_array($str))
	{
		foreach ($str as $key => $val)
		{
			$str[$key] = UTF8::str_ireplace($search, $replace, $val, $count);
		}
		return $str;
	}

	if (is_array($search))
	{
		$keys = array_keys($search);

		foreach ($keys as $k)
		{
			if (is_array($replace))
			{
				if (array_key_exists($k, $replace))
				{
					$str = UTF8::str_ireplace($search[$k], $replace[$k], $str, $count);
				}
				else
				{
					$str = UTF8::str_ireplace($search[$k], '', $str, $count);
				}
			}
			else
			{
				$str = UTF8::str_ireplace($search[$k], $replace, $str, $count);
			}
		}
		return $str;
	}

	//$search = UTF8::strtolower($search);
	//$str_lower = UTF8::strtolower($str);

	//$total_matched_strlen = 0;
	$search = mb_convert_encoding($search, 'utf-32', 'utf-8');
	$str = mb_convert_encoding($str, 'utf-32', 'utf-8');
	$i = 0;
    $position = bruteForce($search, $str);                                                                                          // ensure we know position                                                                                           
	                                                                                                                                // of the needle
    $accumulation = "";                                                                                                             // ensure we store the
                                                                                                                                    // accumulated string
    while ($position != -1) {                                                                                                       // ensure we work until
	                                                                                                                                // we don't find $search
		$accumulation .= mb_substr($str, 0, position);                                                                              // ensure we grab the 
		                                                                                                                            // parts of the string
																																	// prior to the match
		$accumulation .= $replace;                                                                                                  // ensure we place the
																																    // relevant replacement at
																																	// the end of the string
        $str = mb_substr($str, position + $needle_length);                                                                          // ensure we eliminate
		                                                                                                                            // the start of the string
																																	// and the needle_length
		$i++;                                                                                                                       // ensure that we update
		                                                                                                                            // the subtotal
		$position = bruteForce($search, $str);                                                                                      // ensure we update the 
		                                                                                                                            // needle position
																																   
	}
	
/* 
	while (preg_match('/(.*?)'.preg_quote($search, '/').'/s', $str_lower, $matches))
	{
		$matched_strlen = strlen($matches[0]);
		$str_lower = substr($str_lower, $matched_strlen);

		$offset = $total_matched_strlen + strlen($matches[1]) + ($i * (strlen($replace) - 1));
		$str = substr_replace($str, $replace, $offset, strlen($search));

		$total_matched_strlen += $matched_strlen;
		$i++;
	}
*/
	$count += $i;
	mb_convert_encoding($str, 'utf-8', 'utf-32');
	return $str;
}

function bruteForce($needle, $haystack, $number_of_bytes_in_encoding = 4, $offset = 0) {                                                // presumably finds needle
	echo strlen($needle);                                                                                                               // in haystack and returns
                                                                                                                                        // the relative (character)
                                                                                                                                        // position	given an offset																																
	
	if (strlen($needle) % $number_of_bytes_in_encoding != 0) {                                                                              // ensure size is possible
		return -1;
	}
	if (strlen($haystack) % $number_of_bytes_in_encoding != 0) {                                                                            // ensure size is possible
		return -1;
	}
	
	$unpacked_needle = unpack($needle);                                                                                             // ensure needle is 
	                                                                                                                                // unpacked
	$unpacked_haystack = unpack($haystack);                                                                                         // ensure haystack is 
	                                                                                                                                // unpacked
																																	
    $needle_length = mb_strlen($needle);                                                                                            // ensure we know the 
                                                                                                                                    // needle length
    $haystack_length = mb_strlen($haystack);																						// ensure we know the
                                                                                                                                    // haystack length
																																	
    if ($needle_length > $haystack_length) {                                                                                        // ensure that we 
        return -1;                                                                                                                  // return no match if
                                                                                                                                    // needle is larger than
                                                                                                                                    // haystack
    }

    for ($i = $offset; $i < haystack_length - needle_length; ++$i) {                                                                // ensure that we iterate
	                                                                                                                                // over the entire 
																																	// haystack???
        $unpacked_haystack_offset = $i * 4 + 1;                                                                                     // ensure that we know
		                                                                                                                            // where in the unpacked 
																																	// haystack we are
																																	
	    $unpacked_needle_offset = 1;                                                                                                // ensure that we start
		                                                                                                                            // at the beginning of
																																	// the needle
		$number_of_matched_characters = 0;                                                                                          // ensure that we know
		                                                                                                                            // how many characters 
		  																															// match
		while (bruteEqual($unpacked_needle[$unpacked_needle_offset],                                                                // ensure that we iterate
		                  $unpacked_needle[$unpacked_needle_offset + 1],                                                            // while the characters 
                          $unpacked_needle[$unpacked_needle_offset + 2],                                                            // are matching and move
                          $unpacked_needle[$unpacked_needle_offset + 3],                                                            // the offsets and 
                          $unpacked_haystack[$unpacked_haystack_offset],                                                            // increment too
                          $unpacked_haystack[$unpacked_haystack_offset + 1],						                                
						  $unpacked_haystack[$unpacked_haystack_offset + 2],
						  $unpacked_haystack[$unpacked_haystack_offset + 3])){
		    $unpacked_needle_offset += 4;                                                                                           // ensure we increment
			$unpacked_haystack_offset += 4;                                                                                         // the offsets
			$number_of_matched_characters += 1;                                                                                     // ensure we increment
			                                                                                                                        // the number of matched 
																																	// characters
            if ($number_of_matched_characters == $needle_length) {                                                                  // ensure we return a  
			    return $i;                                                                                                          // value that reflects
																																	// where the position of 
																																	// the match is if we 
                                                                                                                                    // have searched through
																																	// the whole needle and 
																																	// the top of the haystack
			}				                                                
    }
 
    return -1;                                                                                                                      // ensure that we return 
	                                                                                                                                // not found                                                                                                                                	
}

}
function bruteEqual($a1, $a2, $a3, $a4, $b1, $b2, $b3, $b4) {                                                                       // presumably decides
                                                                                                                                    // whether the sequences
																																	// a and b are equal
    if ($a1 != $b1) {                                                                                                               // ensure that the value
	    return False;                                                                                                               // is False if the 
																																	// corresponding positions
																																	// are different
	}
    if ($a2 != $b2) {                                                                                                               // ensure that the value
	    return False;                                                                                                               // is False if the 
																																	// corresponding positions
																																	// are different
	}
	if ($a3 != $b3) {                                                                                                               // ensure that the value
	    return False;                                                                                                               // is False if the 
																																	// corresponding positions
																																	// are different
	}
	if ($a4 != $b4) {                                                                                                               // ensure that the value
	    return False;                                                                                                               // is False if the 
																																	// corresponding positions
																																	// are different
	}
	if (canUseBruteLower($a1, $a2, $a3, $a4) && canUseBruteLower($b1, $b2, $b3, $b4)) {                                             // ensure we can use 
	                                                                                                                                // bruteLower
	    if (bruteLower($a1, $a2, $a3, $a4) == bruteLower($b1, $b2, $b3, $b4)) {                                                     // ensure they are the 
		                                                                                                                            // same lowercase value
			return True;                                                                                                            // ensure that True is
                                                                                                                                    // returned
        } 
        else {
            return False;                                                                                                           // ensure that otherwise
        }			                                                                                                                // false is returned
	}
	
	return True;                                                                                                                    // ensure that the value
	                                                                                                                                // is True if the 
                                                                                                                                    // corresponding p
																																	// are all the same or 
																																	// refer to the same
																																	// character

}

function canUseBruteLower($a1, $a2, $a3, $a4) {                                                                                     // presumably determines
                                                                                                                                    // if we can use 
                                                                                                                                    // bruteLower
    if ($a1 != 0) {                                                                                                                 // ensures we are using
        return False;                                                                                                               // proper ranges for 
	}                                                                                                                               // the function
    if ($a2 != 0) {
        return False;
	}
    return True;	
}

function bruteLower($a1, $a2, $a3, $a4) {                                                                                           // presumably returns 
                                                                                                                                    // lower case 4-byte
	$string = pack("C*", $a3, $a4, 0);                                                                                               // ensure that we have the 
	                                                                                                                                // single numeric value 
																																	// of the last two bytes
                                                                                                                                    // placed on a string
    $lower = UTF8::strtolower($string);                                                                                             // ensure that we use the
	                                                                                                                                // Kohana strtolower function
																																	// ~Assumes we should~
	return $lower[0];                                                                                                               // ensure we return the character																																
                                                                                                                                    // ~Assumes validity~
}
