resource "aws_sns_topic" "price_analyzer" {
    name = "${var.app_name}${var.lambda_name}Topic"
}
