new Vue({
  el: '#emptyForm',
  data:  {
	name: '',
	address: [],
	street1: '',
    street2: '',
    city: '',
    state: '',
    zip: ''
  },
  	addAddress: function() {
  		this.address.push({street1: '', street2: '', city: '', state: '',zip: ''});
  	}
})