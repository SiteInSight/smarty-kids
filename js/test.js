$(document).ready(function(){
  var loadTest = {

    setData: function () {
      var testStorage = {
        key: $(".test--form").data("type"),
        storage: {},
        init: function () {
          var result = '';
          words = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM',
            max_position = words.length - 1;
          for (i = 0; i < 5; ++i) {
            position = Math.floor(Math.random() * max_position);
            result = result + words.substring(position, position + 1);
          }
          this.set("ID", result)
        },
        set: function (t, s, a) {
          var o = this;
          this.storage[t] = a ? $.extend(this.storage[t], s) : s,
            localStorage.setItem(o.key, JSON.stringify(this.storage))
        },
        get: function () {
          return this.storage || {}
        },
        clean: function () {
          localStorage.removeItem(this.key)
        }
      };
      testStorage.init()
    },
  
    init: function () {
      var testBlock = $('.test--form'),
        testSteps = $('.test--form__step'),
        testStepsCount = testSteps.length,
        testBtn = $('.test--form__wrapper');
  
      var programmCases = {
        terms: {
          1: 'Скорочтение',
          2: 'Каллиграфия',
          3: 'Арифметика',
          4: 'Робототехника',
        }
      };
  
      if (!testBlock) return false;
      this.click(testBtn, testStepsCount)
    },
  
    click: function (b, l) {
      var clickData = {
        gender: "",
        year: "",
        query: "",
      };
  
      b.click(function () {
        var activeStep = $('.test--form__step.active'),
          nextStep = activeStep.next(),
          activeStepNumber = activeStep.attr('data-hash'),
          dataHash = $(this).attr('data-prop');

        switch (dataHash) {
          case 'man':
            clickData.gender = dataHash;
            closeProgram();
            nextStepLoad(activeStep, nextStep, activeStepNumber);
            break;
          case 'women':
            clickData.gender = dataHash;
            closeProgram([4]);
            nextStepLoad(activeStep, nextStep, activeStepNumber);
            break;
          case 'sm':
            clickData.year = dataHash;
            closeProgram([1, 2, 3]);
            nextStepLoad(activeStep, nextStep, activeStepNumber);
            break;
          case 'md':
            clickData.year = dataHash;
            closeProgram([1, 2, 3]);
            nextStepLoad(activeStep, nextStep, activeStepNumber);
            break;
          case 'lg':
            clickData.year = dataHash;
            closeProgram([1, 2]);
            nextStepLoad(activeStep, nextStep, activeStepNumber);
            break;
          case 'xl':
            clickData.year = dataHash;
            closeProgram([1, 2]);
            nextStepLoad(activeStep, nextStep, activeStepNumber);
            break;
          case 'draw':
            clickData.query = dataHash;
            closeProgram([1, 2, 4]);
            nextStepLoad(activeStep, nextStep, activeStepNumber);
            break;
          case 'written':
            clickData.query = dataHash;
            closeProgram([2, 4]);
            nextStepLoad(activeStep, nextStep, activeStepNumber);
            break;
          case 'games':
            clickData.query = dataHash;
            closeProgram([1, 3]);
            nextStepLoad(activeStep, nextStep, activeStepNumber);
            break;
          case 'singing':
            clickData.query = dataHash;
            closeProgram([2, 4]);
            nextStepLoad(activeStep, nextStep, activeStepNumber);
            break;
          default:
            closeProgram();
            nextStepLoad(activeStep, nextStep, activeStepNumber);
            break;
        }
      });
  
      function nextStepLoad (s, n, c) {
        if (c == '5') {
          return false;
        }
        s.removeClass('active'), n.addClass('active');
      };
  
      function closeProgram (m) {
        console.log(m);
        if (!m) return false;
        m.forEach(function (item) {
          $('.test--form__programms-final[data-loses=' + item + ']').addClass('none');
        })
      };
    },
  
    on: function () {
      this.setData();
      this.init();
    }
  }
  
  loadTest.on()
})