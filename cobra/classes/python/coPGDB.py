# TAB:2
# Check tabs and spaces
#import os
#import sys
#import ply.lex as lex

import pg

class coPGDB:
  __sysprofile = None
  __dbconn = None
	
  def __init__( self, sysprofile ):
    self.__sysprofile = sysprofile
  # end def
  
  def Connect( self ):
    try:
      self.__dbconn = pg.connect( host = self.__sysprofile['dbhost'],
                                  port = self.__sysprofile['dbport'],
                                  dbname = self.__sysprofile['dbname'],
                                  user   = self.__sysprofile['dbuser'],
                                  passwd = self.__sysprofile['dbpass'] )
    except:
      raise Exception( 'CONNECTION_ERROR' )
    # end try
  # end def

  
  def Disconnect( self ):
    try:
      self.__dbconn.close()
    except:
      raise Exception( 'CONNECTION_ERROR' )
    # end try
  # end def


  def _Query( self, query, is_select = True ):
    results = self.__dbconn.query( query )
    # print 'Result: ' + str( results )
    
#    if results == None:
#      raise Exception( 'RESULT_ERROR' )
    # end if
    
  # end def
  
  def Execute( self, query ):
    return self._Query( query, False )
  # end def
  
  
#  def Select( self, query, limit = 100, offset = 0 ):
  # end def
# end class coDBPG
