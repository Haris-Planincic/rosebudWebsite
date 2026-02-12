console.log("messageService loaded");



const messageService = {
  async getOrCreateDirectConversation(otherUserId) {
    const token = localStorage.getItem("jwt_token");
    const res = await fetch(`${API_BASE}/messages/direct/${otherUserId}`, {
      method: "POST",
      headers: {
        "Authorization": `Bearer ${token}`
      }
    });
    if (!res.ok) throw new Error(await res.text());
    return await res.json(); 
  },

  async getMessages(conversationId) {
    const token = localStorage.getItem("jwt_token");
    const res = await fetch(`${API_BASE}/messages/${conversationId}`, {
      method: "GET",
      headers: {
        "Authorization": `Bearer ${token}`
      }
    });
    if (!res.ok) throw new Error(await res.text());
    return await res.json();
  },

  async sendMessage(conversationId, body) {
    const token = localStorage.getItem("jwt_token");
    const res = await fetch(`${API_BASE}/messages/${conversationId}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token}`
      },
      body: JSON.stringify({ body })
    });
    if (!res.ok) throw new Error(await res.text());
    return await res.json(); 
  },
  async getMyConversations() {
  const token = localStorage.getItem("jwt_token");
  const res = await fetch(`${API_BASE}/messages/conversations`, {
    headers: { "Authorization": `Bearer ${token}` }
  });
  if (!res.ok) throw new Error(await res.text());
  return await res.json();
},



async markAsRead(conversationId) {
  const token = localStorage.getItem("jwt_token");
  const res = await fetch(`${API_BASE}/messages/${conversationId}/read`, {
    method: "POST",
    headers: { "Authorization": `Bearer ${token}` }
  });
  if (!res.ok) throw new Error(await res.text());
  return await res.json();
},
async searchUsersByName(query) {
  const token = localStorage.getItem("jwt_token");
  const res = await fetch(`${API_BASE}/users/search?q=${encodeURIComponent(query)}`, {
    method: "GET",
    headers: { "Authorization": `Bearer ${token}` }
  });
  if (!res.ok) throw new Error(await res.text());
  return await res.json();
}

};