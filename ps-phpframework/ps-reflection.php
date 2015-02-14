<?php

/*
    ps-reflection.php <Simple adiction of annotations to object meta-data>
    
    Copyright (C) 2015  ProSource Web Team: Jean Jung - Team leader.
                            

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once('ps-utilities.php');

/**
	* Throwed when something is wrong on reflcting classes for PS framework.
	*/
class PS_ReflectionException extends Exception { }

/** 
* A Annotation in any element of the class.
*/ 
class PS_ReflectionAnnotation 
	implements Reflector 
{
	private $name;
	private $value;
	private $owner;
	
	/**
	  * Defalult constructor.
	  */
	public function __construct($name, $value, $owner) 
	{
		$this->data['name'] = $name;
		$this->data['value'] = $value;
		$this->data['owner'] = $owner;
	}
	
	/**
	  * Implement Reflector function
	  */   
	public static function export() 
        {
                //TODO: 
        }
	/**
	  * Implement Reflector function
	  */  
	public function __toString() 
        {
                //TODO:
        }

	/**
		* Sets the instance object of this annotation.
		*/ 
	public function setValue(PS_Annotation $value)
	{
		$this->value = $value;
	}

	/**
	  * Returns the instance object to this annotation.
	  */
	public function getValue() 
	{
		return $this->value;
	}

	/**
	  * Set the name of this Annotation.
	  */
	public function setName($name) 
	{
		$this->name = $name;
	}

	/**
	  * Returns the name of the Annotation.
	  */
	public function getName()
	{
		return $this->name;
	}

	/**
	  * Sets the owner of this Annotation. Must be an Reflector instance.
          */
	public function setOwner(Reflector $owner) 
	{
		$this->owner = $owner;
	}

	/**
	  * Returns the owner that handle this Annotation. This will be an Reflector instance.
	  */ 
	public function getOwner()
	{
		return $this->owner;
	}
}

/**
  * Class loader to load all annotation classes defined by user.
  */  
class PS_AnnotationClassLoader
{
	const PS_ANNOTATION_DEFAULT_DIR = 'annotations';
        const PS_ANNOTATIONS_FILE = 'ps-annotations.php';
	
	/**
	  * The dirs where to search class files.
	  */
	private static $dirs;
	
	/**
	  * Add dir to search path.
	  */
	public static function addDirToPath($dir) 
	{
		array_push($dirs, $dir);
	}

	/** 
	  * Loads a class file based on actual path
	  */ 
	public static function loadClass($className)
	{
                require_once('ps-annotations.php');
                
                if (class_exists($className)) {
                        return true;
                }
                
		if (file_exists(PS_ANNOTATION_DEFAULT_DIR . '/' . $className . '.php'))
		{
			require_once($dir . '/' . $className . '.php');
			return true;
		}
		
		foreach ($dirs as $dir)
		{
                        $fileName = $dir . '/' . $className . '.php'; 
			if (file_exists($fileName))
			{
				require_once($fileName);
				return true;
			}
		}
		return false;
	}
}

/**
  * A Parser to discover annotation declarations on DocComments of any class or function and 
  * create derived objects to load as metadata on PS Reflection.
  */
class PS_AnnotationParser
	implements PS_Parser
{
	private $ANNOTATION_SIMPLE_DECLARATION = '/@[w]+/';
	private $ANNOTATION_PARAMS_DECLARATION = '/@[w]+[ ]{0,1}([w=, .-<>:]+/';
	
	/**
	  * Extract only the name of a tag.
	  */
	private static function getJustTagName($tag)
	{
		return preg_replace('/[^\w]+/', '', substr($tag, 1));
	}

	/**
	  * Loads the annotation class for the given tag.
          * @return The class name of loaded Class.
          */
	private static function loadClass($tag) 
	{
		$className = PS_AnnotationParser::getJustTagName($tag);
		if (PS_AnnotationClassLoader::loadClass($className)) 
                {
                        return $className;        
                }
                 
                return '';
	}
	
	/**
	  * Parses Simple tag into Annotation instance.
	  */
	private static function parseSimpleTag($tag)
	{
                PS_EchoDebug('ps-reflections.php > PS_AnnotationParser', "Parsing simple tag: $tag");
		$className = PS_AnnotationParser::loadClass($tag);
                PS_EchoDebug('ps-reflections.php > PS_AnnotationParser', "Class name: $className");
                if ($className) 
                {
                        $refClass = new ReflectionClass($className);
                        return $refClass->newInstance($className, array());
                }       
                return null;
	}
        
        /**
          * List the parameters given on a tag
          * @return An array containing the parameters with pairs of key (name of param) and 
          * value (given value).
          */
        private static function listParameters(string $tag) 
        {
                $parameters = array();
                $matches = array();
                if (preg_match_all('/([\w=, .-<>:]+/', $tag, $matches) > 0)
                {
                        foreach($matches as $match) 
                        {
                                $match = preg_replace('/[()]+/', '', $match);
                                $declarations = explode(',', $match);
                                foreach ($declarations as $paramDeclaration) 
                                {
                                        $paramValues = explode('=', $paramDeclaration);
                                        $parameters[$paramValues[0]] = $paramValues[1];
                                }
                        }
                }
                return $parameters;
        }
        
        /**
          * Parses parametrized tag into Annotation instance.
          */
        private static function parseParametrizedTag(string $tag) 
        {
                $className = PS_AnnotationParser::loadClass($tag);
                if ($className != '') 
                {
                        
                        return ReflectionClass($className).newInstance($className, PS_AnnotationParser::listParameters($tag));
                }
        }
	
	/**
          * Parses the DocComments to Annotation list.
          * Allows string (The docComment) 
          * @return array of PS_ReflectionAnnotation
          */
	public static function parse($src) 
	{
                PS_EchoDebug('ps-reflections.php > PS_AnnotationParser', "Parsing DocComment: $src");
		$annotations = array();
                // TODO: Verify to call PREG_MATCH time by time, instead of PREG_MATCH_ALL in the all SRC. For performance.
                // Parametrized tags
                if (preg_match_all('/@[\w]+[ ]{0,1}\([\w=, .-<>:]+\)/', $src, $tags)) 
                {
                        foreach ($tags[0] as $tag)
                        {
                                PS_EchoDebug('ps-reflections.php > PS_AnnotationParser', "Tag found: $tag");
                                $annotation = PS_AnnotationParser::parseParametrizedTag($tag);
                                if ($annotation != null) 
                                {
                                        array_push($annotations, $annotation);
                                }
                        }
                }else
                {
                        PS_EchoDebug('ps-reflections.php > PS_AnnotationParser', "None Annotation with params.");
                }
                // Simple Tags
                if (preg_match_all('/@[\w]+/', $src, $tags)) 
                {
                        foreach ($tags[0] as $tag)
                        {
                                PS_EchoDebug('ps-reflections.php > PS_AnnotationParser', "Tag found: $tag");
                                $annotation = PS_AnnotationParser::parseSimpleTag($tag);
                                if ($annotation != null) 
                                {
                                        array_push($annotations, $annotation);
                                }
                        }
                }else
                {
                        PS_EchoDebug('ps-reflections.php > PS_AnnotationParser', "None Simple Annotation.");
                }
		return $annotations;
	}
}

/**
  * Reflection properties for PS Framework.
  */
class PS_ReflectionProperty extends ReflectionProperty
{
        /**
          * @return The annotations declared for this class. Only the annotations
          * declared in the DocComment of the property will be parsed.
          */
        public function getAnnotations()
        {
                return PS_AnnotationParser::parse($this->getDocComment());
        }
}

/**
  * Reflection classes for PS Framework.
  */
class PS_ReflectionClass extends ReflectionClass 
{

        private $annotations; 
        /**
          * @return The annotations declared for this class. Only the
          * annotations declared in the DocComment of the class will be parsed.
          * The return of this function is cached then you can call multiple times.
          * The information returned is a wrapper for annotations. It's a metadata.
          * You can obviusly access your annotation object instance with getValue() method on
          * this metadata instances.
          */
	public function getAnnotations() 
	{
                if (!$this->annotations)
                        $this->annotations = PS_AnnotationParser::parse($this->getDocComment());
                return $this->annotations;
	}
        
        /**
          * @return The annotation instance for it's name, if any, null ortherwise.
          */
        public function getAnnotation($annotationName)
        {
                $annotations = $this->getAnnotations();
                $refAnnotation = $annotations[$annotationName];
                if ($refAnnotation)
                {
                        return $refAnnotation->getValue();
                }
                return null;
        }
        
        /**
          * @return The PS_Reflections properties for this class. 
          * @see ReflactionClass#getProperties([int $filter]) for more detais.
          */
       public function getProperties ($filter = null)
       {
               // TODO: Verify how to exclude the loop necessity.
               $properties = parent::getProperties($filter);
               $return = array();
               foreach ($properties as $property)
               {
                       array_push($return, PS_ReflectionProperty($property));
               }
               return $return; 
       }
}
?>