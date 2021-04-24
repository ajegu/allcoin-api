resource "aws_sns_topic" "binance_order_analyzer" {
    name = "${var.app_name}-${var.lambda_name}Topic"
}
