<?php
class XCube_Theme
{
	var $mName = null;
	var $mDirname = null;
	var $mDepends = array();
	var $mVersion = null;
	var $mUrl = null;
	var $mRenderSystemName = null;
	var $mScreenShot = null;
	var $mDescription = null;
	var $mFormat = null;
	var $mAuthor = null;
	var $mLicence = null;
	var $mLicense = null;
	var $_mManifesto = array();
	function loadManifesto($file)
	{
		if (file_exists($file)) {
			$this->_mManifesto = parse_ini_file($file, true);
			$this->mName = isset($this->_mManifesto['Manifesto']['Name']) ? $this->_mManifesto['Manifesto']['Name'] : "";
			$this->mDepends = isset($this->_mManifesto['Manifesto']['Depends']) ? $this->_mManifesto['Manifesto']['Depends'] : "";
			$this->mVersion = isset($this->_mManifesto['Manifesto']['Version']) ? $this->_mManifesto['Manifesto']['Version'] : "";
			$this->mUrl = isset($this->_mManifesto['Manifesto']['Url']) ? $this->_mManifesto['Manifesto']['Url'] : "";
			$this->mRenderSystemName = isset($this->_mManifesto['Theme']['RenderSystem']) ? $this->_mManifesto['Theme']['RenderSystem'] : "";
			$this->mAuthor = isset($this->_mManifesto['Theme']['Author']) ? $this->_mManifesto['Theme']['Author'] : "";
			if (isset($this->_mManifesto['Theme']['ScreenShot'])) {
				$this->mScreenShot = $this->_mManifesto['Theme']['ScreenShot'];
			}
			if (isset($this->_mManifesto['Theme']['Description'])) {
				$this->mDescription = $this->_mManifesto['Theme']['Description'];
			}
			$this->mFormat = isset($this->_mManifesto['Theme']['Format']) ? $this->_mManifesto['Theme']['Format'] : "";
			if (isset($this->_mManifesto['Theme']['License'])) {
				$this->mLicense = $this->_mManifesto['Theme']['License'];
				$this->mLicence = $this->_mManifesto['Theme']['License'];
			}
			elseif (isset($this->_mManifesto['Theme']['Licence'])) { 
				$this->mLicense = $this->_mManifesto['Theme']['Licence'];
				$this->mLicence = $this->_mManifesto['Theme']['Licence'];
			}
			return true;
		}
		else {
			return false;
		}
	}
}
?>
