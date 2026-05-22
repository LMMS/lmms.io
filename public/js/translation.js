function textNodesUnder(element) {
	const children = [];
	const walker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT);
	while (walker.nextNode()) {
		children.push(walker.currentNode);
	}
	return children;
}

;(async function () {
	for (const language of navigator.languages) {
		const response = await fetch(`/translations/messages.${language.replace('-', '_')}.xlf.json`);
		if (response.status == 200) {
			const messages = await response.json();
			for (const text of textNodesUnder(document.body)) {
				const original = text.textContent.trim();
				if (messages[original]) {
					text.textContent = messages[original].string;
				}
			}
			for (const element of document.body.querySelectorAll("p")) {
				const original = element.innerHTML.trim();
				if (messages[original]) {
					element.innerHTML = messages[original].string;
				}
			}
			break;
		}
	}
})();
