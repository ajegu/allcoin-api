resource "aws_sns_topic" "binance_order_analyzer" {
    name = "${var.app_name}-${var.lambda_name}Topic"
}

output "binance_order_analyzer_topic_arn" {
    value = aws_sns_topic.binance_order_analyzer.arn
}
