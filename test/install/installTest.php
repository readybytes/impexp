<?php

class InstallTest extends XiSelTestCase
{
  function getSqlPath()
  {
      return dirname(__FILE__).'/sql/'.__CLASS__;
  }

  protected $collectCodeCoverageInformation = FALSE;

}
