const vueContainers = document.querySelectorAll('.vuejs');

vueContainers.forEach((vueContainer) => {
  const application = new Vue({
    el: vueContainer,
    data: {
      test: 'hello world',
    },
    vuetify: new Vuetify(),
  })

  // debug all components from application

  const components = application.$options.components;
  console.log('%cvuejs-runner.js :: 12 =============================', 'color: #f00; font-size: 1rem');
  console.log(application.$children);


});
