"use client"

import {
  createContext,
  useContext,
  useState,
  useEffect,
  useCallback,
  type ReactNode,
} from "react"
import type { User, AuthState } from "@/types/auth"
import {
  login as apiLogin,
  register as apiRegister,
  getMe,
  verifyToken,
  setTokens,
  clearTokens,
  type ApiRequestError,
} from "@/lib/api"

interface AuthContextType extends AuthState {
  login: (email: string, password: string) => Promise<void>
  register: (name: string, email: string, password: string) => Promise<void>
  logout: () => void
  refreshUser: () => Promise<void>
  hasRole: (role: string) => boolean
  hasPermission: (permission: string) => boolean
}

const AuthContext = createContext<AuthContextType | null>(null)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [state, setState] = useState<AuthState>({
    user: null,
    roles: [],
    permissions: [],
    accessToken: null,
    refreshToken: null,
    isAuthenticated: false,
    isLoading: true,
  })

  const fetchUser = useCallback(async () => {
    try {
      const res = await getMe()
      if (res.success && res.data) {
        const roles = res.data.roles.map((r) =>
          typeof r === "string" ? r : r.name
        )
        const permissions = res.data.permissions.map((p) =>
          typeof p === "string" ? p : p.name
        )
        setState((prev) => ({
          ...prev,
          user: res.data.user,
          roles,
          permissions,
          isAuthenticated: true,
          isLoading: false,
        }))
      }
    } catch {
      setState((prev) => ({
        ...prev,
        user: null,
        roles: [],
        permissions: [],
        isAuthenticated: false,
        isLoading: false,
        accessToken: null,
        refreshToken: null,
      }))
      clearTokens()
    }
  }, [])

  // On mount, try to restore session from sessionStorage
  useEffect(() => {
    const storedAccess = sessionStorage.getItem("sa_access_token")
    const storedRefresh = sessionStorage.getItem("sa_refresh_token")

    if (storedAccess && storedRefresh) {
      setTokens(storedAccess, storedRefresh)
      setState((prev) => ({
        ...prev,
        accessToken: storedAccess,
        refreshToken: storedRefresh,
      }))

      verifyToken()
        .then((res) => {
          if (res.valid) {
            fetchUser()
          } else {
            clearTokens()
            sessionStorage.removeItem("sa_access_token")
            sessionStorage.removeItem("sa_refresh_token")
            setState((prev) => ({ ...prev, isLoading: false }))
          }
        })
        .catch(() => {
          clearTokens()
          sessionStorage.removeItem("sa_access_token")
          sessionStorage.removeItem("sa_refresh_token")
          setState((prev) => ({ ...prev, isLoading: false }))
        })
    } else {
      setState((prev) => ({ ...prev, isLoading: false }))
    }
  }, [fetchUser])

  const login = useCallback(
    async (email: string, password: string) => {
      const res = await apiLogin({ email, password })
      if (res.success && res.data) {
        const { access_token, refresh_token } = res.data
        setTokens(access_token, refresh_token)
        sessionStorage.setItem("sa_access_token", access_token)
        sessionStorage.setItem("sa_refresh_token", refresh_token)
        setState((prev) => ({
          ...prev,
          accessToken: access_token,
          refreshToken: refresh_token,
        }))
        await fetchUser()
      }
    },
    [fetchUser]
  )

  const registerUser = useCallback(
    async (name: string, email: string, password: string) => {
      const res = await apiRegister({ name, email, password })
      if (!res.success) {
        throw new Error(res.message || "Registration failed")
      }
    },
    []
  )

  const logout = useCallback(() => {
    clearTokens()
    sessionStorage.removeItem("sa_access_token")
    sessionStorage.removeItem("sa_refresh_token")
    setState({
      user: null,
      roles: [],
      permissions: [],
      accessToken: null,
      refreshToken: null,
      isAuthenticated: false,
      isLoading: false,
    })
  }, [])

  const hasRole = useCallback(
    (role: string) => state.roles.includes(role),
    [state.roles]
  )

  const hasPermission = useCallback(
    (permission: string) => state.permissions.includes(permission),
    [state.permissions]
  )

  return (
    <AuthContext.Provider
      value={{
        ...state,
        login,
        register: registerUser,
        logout,
        refreshUser: fetchUser,
        hasRole,
        hasPermission,
      }}
    >
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth(): AuthContextType {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error("useAuth must be used within an AuthProvider")
  }
  return context
}
