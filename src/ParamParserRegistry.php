<?php

namespace BlueSpice\EchoConnector;

use BlueSpice\ExtensionAttributeBasedRegistry;

class ParamParserRegistry extends ExtensionAttributeBasedRegistry {
	//Possibly move this to BSF
	public function hasKey( $key ) {
		$allKeys = array_keys( $this->extensionRegistry->getAttribute( $this->attribName ) );
		if( in_array( $key, $allKeys ) ) {
			return true;
		}
		return false;
	}
}