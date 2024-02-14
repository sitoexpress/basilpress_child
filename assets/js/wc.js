function basil_plus_minus() {

  var current_qty
  var step
  var parent
  var new_qty
  var change_qty_to
  var qty_wrap
  var is_woocommerce = $('body.woocommerce').length
  var is_cart = $('body.woocommerce-cart').length
  var is_checkout = $('body.woocommerce-checkout').length

  // wooSetQty works in the cart and single product

  function init() {

    $('.site .quantity input').each(function() {

      steps = parseInt($(this).attr('step'))
      if(!steps) steps = 1

      parent = $(this).closest('quantity')

      if(!$(this).hasClass('quantity-active')) {

        $(this).wrap('<span class="quantity-wrapper"></span>').addClass('quantity-active')
        qty_wrap = $(this).closest('.quantity-wrapper')
        $(qty_wrap).append('<span class="increment"></span>')
        $(qty_wrap).prepend('<span class="decrement"></span>')

      }

      $( ".decrement").unbind( "click" )
      $( ".increment").unbind( "click" )

    })

    $('.decrement').click(function(){
      steps = parseInt($(this).closest('.quantity-wrapper').find('.qty').attr('step'))
      if(!steps) steps = 1
      change_qty_to = $(this).closest('.quantity-wrapper').find('.qty')
      current_qty = $(change_qty_to).val()
      if(current_qty > 0) {
        new_qty = parseFloat(current_qty) - steps
        $(change_qty_to).val(new_qty).change()
      }
      if(is_cart) {
        $('.actions .button').prop('disabled',false)
      }
    })

    $('.increment').click(function(){
      steps = parseInt($(this).closest('.quantity-wrapper').find('.qty').attr('step'))
      if(!steps) steps = 1
      change_qty_to = $(this).closest('.quantity-wrapper').find('.qty')
      current_qty = $(change_qty_to).val()

        new_qty = parseFloat(current_qty) + steps
        $(change_qty_to).val(new_qty).change()

      if(is_cart) {
        $('.actions .button').prop('disabled',false)
      }

    })

  }

  init()

  $(document.body).on('updated_cart_totals updated_checkout', function(){
    init()
  })

}

function add_to_cart_dynamics() {

  /* Add to cart in variable products */

  $(document).on('click', '.single_add_to_cart_button:not(.disabled)', function (e) {

    var $thisbutton = $(this),
            $form = $thisbutton.closest('form.cart'),
            //quantity = $form.find('input[name=quantity]').val() || 1,
            //product_id = $form.find('input[name=variation_id]').val() || $thisbutton.val(),
            data = $form.find('input:not([name="product_id"]), select, button, textarea').serializeArrayAll() || 0;
  
    $.each(data, function (i, item) {
      if (item.name == 'add-to-cart') {
        item.name = 'product_id';
        item.value = $form.find('input[name=variation_id]').val() || $thisbutton.val();
      }
    });
  
    e.preventDefault();
  
    $(document.body).trigger('adding_to_cart', [$thisbutton, data]);
  
    $.ajax({
      type: 'POST',
      url: woocommerce_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
      data: data,
      beforeSend: function (response) {
        $thisbutton.removeClass('added').addClass('loading');
      },
      complete: function (response) {
        $thisbutton.addClass('added').removeClass('loading');
      },
      success: function (response) {
  
        if (response.error && response.product_url) {
          window.location = response.product_url;
          return;
        }
  
        $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
      },
    });
  
    return false;
  
  });

	/* After Add To Cart Behavior */

	$( document.body ).on( 'added_to_cart', function(){
		var buy_wrap = $('.buy-button-wrapper')

		if(buy_wrap.length) {
			setTimeout(function(){

				var Atc = $('.added_to_cart', buy_wrap)

				buy_wrap.each(function(){

					if($('.remove-added-to-cart', this).length) return

					$('.added_to_cart', this).append('<span class="remove-added-to-cart"></span>')
					$('.added_to_cart', this).addClass('a-appear')

					$('.remove-added-to-cart', this).click(function(e){

						e.preventDefault()

						var thisAtc = $(this).closest('.added_to_cart')
						var thisBuyButt = $(this).closest('.buy-button-wrapper').find('.added')

						$(this).closest('.buy-button-wrapper').find('.go-to-product').toggleClass('a-appear a-disappear')

						thisAtc.remove()
						thisBuyButt.removeClass('added')

					})

				})

			}, 250)
		}
	})

	$('.buy-button-wrapper .add_to_cart_button').click(function(){
		$('+ .go-to-product', this).toggleClass('a-appear a-disappear')
	})
  
}

function coupon_toggle() {

  let toggles = document.querySelectorAll('.showcoupon');
  let target = document.querySelectorAll('.checkout_coupon');
  let timeout;

  if(target.length == 0 || toggles.length == 0) return;

  target[0].classList.add('closed');

  toggles.forEach(function(toggle) {
    toggle.addEventListener('click', function() {

      timeout = (target[0].classList.contains('closed')) ? 250 : 0;

      setTimeout(function() {
        window.requestAnimationFrame(function() {
          target[0].classList.toggle('closed')
          target[0].classList.toggle('open')
        })
      }, timeout)

    })
  })

}

function vat_toggle() {

  let toggle = document.getElementById('billing_fatt')
  let targets = document.querySelectorAll('.invoice-field')
  let timeout

  if(targets.length == 0 || toggle.length == 0) return

  timeout = 120

  function work() {
    targets.forEach(function(target){

      if(toggle.checked) {

        target.classList.remove('hide')

        setTimeout(function() {
          window.requestAnimationFrame(function() {
            target.classList.remove('a-disappear')
            target.classList.add('a-appear')
          })
        }, timeout)

      } else {

        target.classList.add('a-disappear')
        target.classList.remove('a-appear')
        target.classList.add('hide')

      }
    })
  }

  toggle.addEventListener('click', function() {
    work()
  })

  work()

}

function custom_checkout_and_account() {

  $('.woocommerce-checkout #edit-address').click(function(){
    $('#customer_details select').select2('destroy')
    $(this).closest('.account-box').remove()
    $('#customer_details select').select2()
  })

  $("#ship-to-different-address-checkbox").change(function() {
    if(this.checked) {
      setTimeout(function(){
        $('.shipping_address select').select2('destroy')
        $('.shipping_address select').select2()
      }, 100);
    }
  })

}

$.fn.serializeArrayAll = function () {
  var rCRLF = /\r?\n/g;
  return this.map(function () {
    return this.elements ? jQuery.makeArray(this.elements) : this;
  }).map(function (i, elem) {
    var val = jQuery(this).val();
    if (val == null) {
      return val == null
      //next 2 lines of code look if it is a checkbox and set the value to blank 
      //if it is unchecked
    } else if (this.type == "checkbox" && this.checked === false) {
      return {name: this.name, value: this.checked ? this.value : ''}
      //next lines are kept from default jQuery implementation and 
      //default to all checkboxes = on
  } else if (this.type === 'radio') {
      if (this.checked) {
        return {name: this.name, value: this.checked ? this.value : ''};
      }  	  
    } else {
      return jQuery.isArray(val) ?
              jQuery.map(val, function (val, i) {
                return {name: elem.name, value: val.replace(rCRLF, "\r\n")};
              }) :
              {name: elem.name, value: val.replace(rCRLF, "\r\n")};
    }
  }).get();
};

document.addEventListener("DOMContentLoaded", function() {
  basil_plus_minus()
  add_to_cart_dynamics()
  coupon_toggle()
  vat_toggle()
  custom_checkout_and_account()
})
