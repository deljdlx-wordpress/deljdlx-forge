console.log('%c' + 'Loading customizer loader ', 'color: #a08; font-size: 1rem; background-color:#fff');


function registerCustomizer(variableName, callback) {
  document.addEventListener("DOMContentLoaded", function() {

    console.log('%c' + 'Register callback for customize variable : ' + variableName, 'color: #a08; font-size: 1rem; background-color:#fff');

    wp.customize(
      variableName,
      function(value) {
        value.bind(function(newValue) {
          console.log(newValue);
          callback(newValue);
        });
      }
    );
  });
}


function registerDebugCustomizer(variableName, selector) {
  registerCustomizer(variableName, function(value) {
    console.log('%ccustomizer-loader.js :: 24 =============================', 'color: #f00; font-size: 1rem');
    console.log(value);
  })
}


function registerInnerHTMLCustomizer(variableName, selector) {
  registerCustomizer(variableName, function(value) {

    let element = document.querySelector(selector);

    for(let child of element.childNodes) {
      if(child.classList) {
        if(!child.classList.contains('customize-partial-edit-shortcut')) {
          child.remove();
        }
      }
      else {
        child.remove();
      }
    }

    let container = document.createElement('div');
    container.innerHTML = value;

    for(let child of container.childNodes) {
      element.appendChild(child);
    }
  })
}

function registerImageCustomizer(variableName, selector) {
  registerCustomizer(variableName, function(value) {
    document.querySelector(selector).setAttribute('src', value);
  })
}

function registerBackgroundImageCustomizer(variableName, selector) {
  registerCustomizer(variableName, function(value) {
    document.querySelector(selector).style.backgroundImage = 'url(' + value + ')';
  })
}


function registerBackgroundColorCustomizer(variableName, selector) {
  registerCustomizer(variableName, function(value) {
    document.querySelector(selector).style.backgroundColor = value;
  })
}

function registerColorCustomizer(variableName, selector) {
  registerCustomizer(variableName, function(value) {
    document.querySelectorAll(selector).forEach(function(element) {
      element.style.color = value;
    });
  })
}

function registerCssVariableCustomizer(variableName, selector) {
  registerCustomizer(variableName, function(value) {
    console.log('%ccustomizer-loader.js :: 77 =============================', 'color: #f00; font-size: 1rem');
    console.log(variableName + '::' + value);

    const head = document.querySelector('head');

    const styleTagId = 'customize-css-variable-' + variableName;
    let styleTag = document.getElementById(styleTagId);
    if(styleTag) {
      styleTag.remove();
    }
    styleTag = document.createElement('style');
    styleTag.id = styleTagId;
    styleTag.innerHTML = `${selector} { --${variableName}: ${value} !important; }`;
    head.appendChild(styleTag);
  })
}

function registerCssVariableInPxCustomizer(variableName, selector) {
  registerCustomizer(variableName, function(value) {
    console.log(variableName + '::' + value);
    const head = document.querySelector('head');

    const styleTagId = 'customize-css-variable-' + variableName;
    let styleTag = document.getElementById(styleTagId);
    if(styleTag) {
      styleTag.remove();
    }
    styleTag = document.createElement('style');
    styleTag.id = styleTagId;
    styleTag.innerHTML = `${selector} { --${variableName}: ${value}px !important; }`;
    head.appendChild(styleTag);
  })
}



function registerTypographyCustomizerCustomizer(variableName, selecter) {

  registerCustomizer(variableName, function(value) {
    let fontFamily = value['font-family'];
    let fontSize = value['font-size'];
    let color = value['color']
    let weight = value['font-weight']
    let textAlign = value['text-align'];

    setCssVariable(variableName + '-font-family', fontFamily, '.taverne');
    setCssVariable(variableName + '-font-size', fontSize, '.taverne');
    setCssVariable(variableName + '-font-color', color, '.taverne');
    setCssVariable(variableName + '-font-weight', weight, '.taverne');
    setCssVariable(variableName + '-text-align', textAlign, '.taverne');
  });
}





function registerCssColorVariableCustomizer(variableName, selector) {
  registerCustomizer(variableName, function(value) {
    console.log('%ccustomizer-loader.js :: 77 =============================', 'color: #f00; font-size: 1rem');
    console.log(variableName + '::' + value);

    const head = document.querySelector('head');

    const styleTagId = 'customize-css-variable-' + variableName;
    let styleTag = document.getElementById(styleTagId);
    if(styleTag) {
      styleTag.remove();
    }
    styleTag = document.createElement('style');
    styleTag.id = styleTagId;
    styleTag.innerHTML = `${selector} { --${variableName}: ${value} !important; }`;
    head.appendChild(styleTag);
  })
}


function registerCssOkclhColorVariableCustomizer(variableName, selector) {
  registerCustomizer(variableName, function(value) {


    let converted = new Color(value);
    console.log(converted.to('oklch').toString());
    value = converted.to('oklch').toString();
    // remove oklch()
    value = value.replace('oklch(', '');
    value = value.replace(')', '');


    // value = hexToOKLCH(value);
    setCssVariable(variableName, value, selector);
    return;

    console.log('%ccustomizer-loader.js :: 77 =============================', 'color: #f00; font-size: 1rem');
    console.log(variableName + '::' + value);
    const head = document.querySelector('head');

    const styleTagId = 'customize-css-variable-' + variableName;
    let styleTag = document.getElementById(styleTagId);
    if(styleTag) {
      styleTag.remove();
    }

    styleTag = document.createElement('style');
    styleTag.id = styleTagId;
    styleTag.innerHTML = `${selector} { --${variableName}: ${value} !important; }`;
    head.appendChild(styleTag);
    console.log('%ccustomizer-loader.js :: 171 =============================', 'color: #f00; font-size: 1rem');
    console.log(styleTag);
  });
}



document.addEventListener('DOMContentLoaded', function(event) {
  document.addEventListener('click', function(event) {
    // console.log(event.currentTarget);
    console.log(event.target);
  });
});

// ======================================================

const setCssVariable = (variableName, value, selector) => {
  console.log('%ccustomizer-loader.js :: 77 =============================', 'color: #f00; font-size: 1rem');
  console.log(variableName + '::' + value);

  const head = document.querySelector('head');

  const styleTagId = 'customize-css-variable-' + variableName;
  let styleTag = document.getElementById(styleTagId);
  if(styleTag) {
    styleTag.remove();
  }
  styleTag = document.createElement('style');
  styleTag.id = styleTagId;
  styleTag.innerHTML = `${selector} { --${variableName}: ${value} !important; }`;
  head.appendChild(styleTag);
}






