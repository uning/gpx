
/*
<div class="controls">
<button onclick="Log.clear()">Clear</button>
</div>
<div id="__log"></div>
</div>
//log
 Log.init(document.getElementById("log"),'debug');
 Log.debug("log inited");

 var data = {df:"log inited",dfff:'dfdfd'};
 Log.debug.bind('myll')(data);
 Log.debug.bind('myll')("nnallldlldf");
*/
var Log = {
  levels: ['error', 'info', 'debug','fine'],
  root: null,
  count: 0,

  impl: function(level) {
    return function() {
		//console.log('in log',arguments);
        Log.write(level, Array.prototype.slice.apply(arguments));
    };
  },

  write: function(level, args) {
    if (level > Log.level) {
        return;
    }
	var name = Log.levels[level],
      hd = args.shift(),
	  f = console[name];
	if(typeof f === typeof setTimeout ){
		f.call(console,name,hd,args);
	}else
		console.log(name,hd,args);

	if(!Log.root)
		return;
    bd = Log.dumpArray(args),
    Log.writeHTML(level, hd, bd);
  },

  dumpArray: function(args) {
    var bd = '';
    for (var i=0, l=args.length; i<l; i++) {
      if (bd) {
        bd += '<hr>';
      }
      bd += jsDump.parse(args[i]);
    }
    return bd;
  },

  writeHTML: function(level, hd, bd) {

    var entry = document.createElement('div');
    entry.className = 'log-entry log-' + Log.levels[level];
    entry.innerHTML = Log.genBare(hd, bd);
    Log.root.insertBefore(entry, Log.root.firstChild);
  },

  genBare: function(hd, bd) {
    return (
      '<div class="hd">' +
        '<span class="toggle">&#9658;</span> ' +
        '<span class="count">' + (++Log.count) + '</span> ' +
        hd +
      '</div>' +
      (bd ? '<div class="bd" style="display: none;">' + bd + '</div>' : '')
    );
  },

  genHTML: function(hd, bd) {
    return '<div class="log-entry">' + Log.genBare(hd, bd) + '</div>';
  },

  clear: function() {
    Log.root.innerHTML = '';
    Log.count = 0;
  },

  getLevel: function(name) {
    for (var i=0, l=Log.levels.length; i<l; i++) {
      if (name == Log.levels[i]) {
        return i;
      }
    }
    return l; // max level
  },

  init: function(root, levelName) {
    jsDump.HTML = true;
    Log.level = Log.getLevel(levelName);
	//*
    for (var i=0, l=Log.levels.length; i<l; i++) {
      var name = Log.levels[i];
	  //console.log('in Log.init',i,name);
      Log[name] = Log.impl(i);
      Log[name].bind = function(title) {
        var self = this;
        return function() {
          var args = Array.prototype.slice.apply(arguments);
          args.unshift(title);
          self.apply(null, args);
        };
      };
    }
    //*/
  //levels: ['error', 'info', 'debug','fine'],
  Log.error=Log['error']
  Log.info=Log['info']
  Log.debug=Log['debug']
  Log.fine=Log['fine']
	
  Log.root = root || null

  if(root){
  }else{
      var entry = document.createElement('div');
      entry.style.cssText= "position:relative;float:right;width:200px;";
      entry.innerHTML = '<button onclick="Log.clear()">Clear</button>'
      +'<div id="__log"></div>';
      document.body.insertBefore(entry, document.body.firstChild);
      Log.root = root  = document.getElementById('__log');
  }
	  root.style.height = (
		  (window.innerHeight || document.documentElement.clientHeight)
		  + 'px'
	  );
	  Delegator.listen('.log-entry .toggle', 'click', function() {
		  try {
			  var style = this.parentNode.nextSibling.style;
			  if (style.display == 'none') {
				  style.display = 'block';
				  this.innerHTML = '&#9660;';
			  } else {
				  style.display = 'none';
				  this.innerHTML = '&#9658;';
			  }
		  } catch(e) {
			  // ignore, the body is probably missing
		  }
	  });
      //Log.info("log init");
  }

};
