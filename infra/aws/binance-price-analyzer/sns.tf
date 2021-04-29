resource "aws_sns_topic" "binance_price_analyzer" {
    name = "${var.app_name}-${var.lambda_name}Topic"
}

output "binance_price_analyzer_topic_arn" {
    value = aws_sns_topic.binance_price_analyzer.arn
}
