# TAB:2
# Check tabs and spaces
import os
import sys
import ply.lex as lex

class coPHP:
  _php_arrays = {}


  def __init__( self, filename ):
		self._filename = filename
	# end def
	
  def reset( self ):
    self._in_php       = False
    self._in_comment   = False
    self._value	       = 0
    self._id		       = ''
    self._array_id	   = ''
    self._in_array_id  = False
    self._was_op       = False
    self._was_array_id = False
  # end def    


  # token list
  tokens = ( 'PHP_BEGIN',
		     		 'PHP_END',
			       'COMMENT_LINE',
			       'COMMENT_BEGIN',
			       'COMMENT_END',
			       'PHP_ARRAY_ID',
			       'PHP_ARRAY_ID_BEGIN',
			       'PHP_ARRAY_ID_END',             
			       'PHP_ASSIGN_OP',
			       'PHP_NUM',
			       'PHP_STR',
			       'PHP_ID',
             'PHP_SEP' )



  # simple rules

  # complex rules
  def t_COMMENT_LINE( self, t ):
		r'//.*'
		pass
  # end def


  def t_COMMENT_BEGIN( self, t ):
		r'/\*'
		self._in_comment = True
		return t
  # end def

    
  def t_COMMENT_END( self, t ):
		r'\*/'
		self._in_comment = False	
		return t
  # end def


  def t_PHP_BEGIN( self, t ):
		r'<\?php'
		self._in_php = True
		return t
  # end def

    
  def t_PHP_END( self, t ):
		r'\?>'
		self._in_php = False	
		return t
	# end def


  def t_PHP_STR( self, t ):
    r'\'[^\']*\''
    t.value = str(t.value).strip("\'")
    if self._in_array_id:
      self._was_array_id = True
      self._array_id = t.value
    self._value = t.value
    return t
	# end def
	

  def t_PHP_NUM( self, t ):
		r'[0-9]+'
		t.value = int(t.value)
		self._value = t.value
		return t
  # end def


  def t_PHP_ID( self, t ):
		r'\$[a-zA-Z_][a-zA-Z0-9_-]*'
		t.value = str(t.value).strip('$')
		self._id = t.value
		try:
			self._php_arrays[self._id]
		except:
			self._php_arrays[self._id] = {}
		# end try
		return t
	# end def


  def t_PHP_ARRAY_ID_BEGIN( self, t ):
		r'\['
		self._in_array_id = True
		return t
  # end def


  def t_PHP_ARRAY_ID_END( self, t ):
    r'\]'
    self._in_array_id = False
    return t
  # end def



  def t_PHP_ASSIGN_OP( self, t ):
		r'='
		self._was_op = True
		# t.value = str(t.value)
		return t
  # end def
  
  
  def t_PHP_SEP( self, t ):
		r';'
		if self._was_op and self._was_array_id:
			# print (self._id, self._array_id, self._value)
			self._php_arrays[self._id][self._array_id] = self._value
			self.reset()
		return t
  # end def

  
  def t_newline( self, t ):
    r'\n+'
    t.lexer.lineno += t.value.count("\n")
  # end def


  # ignore white spaces
  # TODO: do it better for tabs
  t_ignore = r' '


  def t_error( self, t ):
	# print("Illegal character '%s'" % t.value[0])
		t.lexer.skip(1)
  # end def


  # constructor
  def build( self, **kwargs ):
		self.lexer = lex.lex(module=self, **kwargs)
  # end def


  def tokenize( self, data ):
		self.lexer.input(data)
		while True:
			tok = self.lexer.token()
			if not tok: break
		# end while
  # end def
  

  def test_tokenize( self, data ):
		self.lexer.input(data)
		while True:
			tok = self.lexer.token()
			if not tok: break
			print tok
		# end while
  # end def
  

  def process( self ):
    try:
      inp = open( self._filename, "r" )
    except:
      print os.path.basename(__file__)
      raise
    self.reset()
    self.build()
    # end try
    for line in inp:
      # self.test_tokenize( line )
      self.tokenize( line )
		# end for
    inp.close()
    return self._php_arrays
  # end def


  def get( self, array_id = 'sysprofile' ):
		# self.process( filename )  
		return self._php_arrays[array_id]
  # end def

# end class coPHP
