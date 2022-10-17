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

    console.log('plus_minus')

  }

  init()

  $(document.body).on('updated_cart_totals updated_checkout', function(){
    init()
  })

}

function add_to_cart_dynamics() {

	//** Ajax Add To Cart Dynamics **//

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

						thisAtc.removeClass('a-appear')
						thisBuyButt.removeClass('added')

						setTimeout(function(){
							thisAtc.remove()
						}, 550)

					})

				})

			}, 100)
		}
	})

	$('.buy-button-wrapper .add_to_cart_button').click(function(){
		$('+ .go-to-product', this).toggleClass('a-appear a-disappear')
	})
}

$(document).ready(function() {
  basil_plus_minus()
  add_to_cart_dynamics()
})
